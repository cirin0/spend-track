import { ref } from 'vue'
import axios from 'axios'
import { useToast } from './useToast'

interface CurrencyRate {
  rate: number
  date: string
}

interface CurrencyRates {
  EUR: CurrencyRate
  USD: CurrencyRate
  timestamp: number
}

interface NBUResponse {
  cc: string
  rate: number
  exchangedate: string
}

const NBU_API_URL = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json'
const CACHE_KEY = 'currency_rates'
const CACHE_TTL = 24 * 60 * 60 * 1000 // 24 hours in milliseconds

export function useCurrency() {
  const rates = ref<CurrencyRates | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const ratesUnavailable = ref(false)
  const usingCache = ref(false)
  const toast = useToast()

  /**
   * Check if cached rates are still valid (less than 24 hours old)
   */
  function isCacheValid(): boolean {
    try {
      const cached = localStorage.getItem(CACHE_KEY)
      if (!cached) return false

      const data = JSON.parse(cached) as CurrencyRates
      const now = Date.now()
      const age = now - data.timestamp

      return age < CACHE_TTL
    } catch (e) {
      console.error('Error checking cache validity:', e)
      return false
    }
  }

  /**
   * Load rates from localStorage cache
   */
  function loadFromCache(): CurrencyRates | null {
    try {
      const cached = localStorage.getItem(CACHE_KEY)
      if (!cached) return null

      return JSON.parse(cached) as CurrencyRates
    } catch (e) {
      console.error('Error loading from cache:', e)
      return null
    }
  }

  /**
   * Save rates to localStorage cache
   */
  function saveToCache(data: CurrencyRates): void {
    try {
      localStorage.setItem(CACHE_KEY, JSON.stringify(data))
    } catch (e) {
      console.error('Error saving to cache:', e)
    }
  }

  /**
   * Fetch exchange rates from NBU API or use cached rates
   */
  async function fetchRates(): Promise<void> {
    // Check cache first
    if (isCacheValid()) {
      const cached = loadFromCache()
      if (cached) {
        rates.value = cached
        usingCache.value = true
        ratesUnavailable.value = false
        return
      }
    }

    loading.value = true
    error.value = null
    usingCache.value = false
    ratesUnavailable.value = false

    try {
      const response = await axios.get<NBUResponse[]>(NBU_API_URL, {
        timeout: 10000, // 10 second timeout
      })

      // Parse NBU response and extract EUR and USD rates
      const eurData = response.data.find((item) => item.cc === 'EUR')
      const usdData = response.data.find((item) => item.cc === 'USD')

      if (!eurData || !usdData) {
        throw new Error('EUR or USD rates not found in NBU response')
      }

      const newRates: CurrencyRates = {
        EUR: {
          rate: eurData.rate,
          date: eurData.exchangedate,
        },
        USD: {
          rate: usdData.rate,
          date: usdData.exchangedate,
        },
        timestamp: Date.now(),
      }

      rates.value = newRates
      saveToCache(newRates)
    } catch (e) {
      console.error('Error fetching rates from NBU:', e)
      error.value = 'Failed to fetch currency rates'

      // Fallback to cached rates even if expired
      const cached = loadFromCache()
      if (cached) {
        rates.value = cached
        usingCache.value = true
        error.value = 'Using cached currency rates'
        toast.warning('Не вдалося завантажити курси валют. Використовуються збережені дані.')
      } else {
        // No cache available, rates will remain null
        ratesUnavailable.value = true
        error.value = 'Currency rates unavailable. Please use UAH.'
        toast.error('Курси валют недоступні. Будь ласка, використовуйте UAH.')
      }
    } finally {
      loading.value = false
    }
  }

  /**
   * Get exchange rate for a specific currency
   * UAH always returns 1.0
   */
  function getRate(currency: 'UAH' | 'EUR' | 'USD'): number {
    if (currency === 'UAH') {
      return 1.0
    }

    if (!rates.value) {
      console.warn('Rates not loaded, returning 1.0')
      return 1.0
    }

    return rates.value[currency]?.rate || 1.0
  }

  /**
   * Convert amount from source currency to UAH
   */
  function convertToUAH(amount: number, fromCurrency: 'UAH' | 'EUR' | 'USD'): number {
    if (fromCurrency === 'UAH') {
      return amount
    }

    const rate = getRate(fromCurrency)
    const converted = amount * rate

    // Round to 2 decimal places
    return Math.round(converted * 100) / 100
  }

  /**
   * Format currency amount with proper symbol and decimal places
   */
  function formatCurrency(amount: number, currency: 'UAH' | 'EUR' | 'USD'): string {
    const symbols: Record<string, string> = {
      UAH: '₴',
      EUR: '€',
      USD: '$',
    }

    const formatted = amount.toFixed(2)
    const symbol = symbols[currency] || currency

    // For UAH, put symbol after amount; for others, before
    if (currency === 'UAH') {
      return `${formatted} ${symbol}`
    }

    return `${symbol}${formatted}`
  }

  return {
    rates,
    loading,
    error,
    ratesUnavailable,
    usingCache,
    fetchRates,
    getRate,
    convertToUAH,
    formatCurrency,
    isCacheValid,
  }
}
