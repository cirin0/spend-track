FROM node:22-alpine

WORKDIR /app

# Copy package files for layer caching
COPY package.json package-lock.json ./

# Use npm ci for deterministic builds
RUN npm ci

# Copy remaining files
COPY . .

EXPOSE 5173

# Run dev server with host 0.0.0.0 for external access
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
