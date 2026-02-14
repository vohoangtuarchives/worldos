# Base stage for dependencies
FROM node:20-alpine AS base

WORKDIR /app

# Copy package files
COPY package*.json ./

# Development dependencies stage
FROM base AS dev-deps
RUN npm install

# Production dependencies stage
FROM base AS prod-deps
RUN npm ci --only=production

# Development stage
FROM dev-deps AS development
ENV NODE_ENV=development \
    PORT=3000 \
    HOSTNAME="0.0.0.0" \
    WATCHPACK_POLLING=true \
    CHOKIDAR_USEPOLLING=true \
    NEXT_TELEMETRY_DISABLED=1 \
    WATCHPACK_POLLING_INTERVAL=1000 \
    CHOKIDAR_INTERVAL=1000

# Copy only necessary files for development
# Source code will be mounted via volume in docker-compose.yml for hot reload
COPY package*.json ./
COPY next.config.ts ./
COPY tsconfig.json ./
COPY public ./public

# Create a default .env.local if it doesn't exist (if .env.example exists)
RUN if [ -f .env.example ] && [ ! -f .env.local ]; then cp -n .env.example .env.local || true; fi

# Expose port for hot reload
EXPOSE 3000

# Start development server with Turbopack for faster hot reload
# Source code will be mounted via volume, so we don't need to COPY it
CMD ["npm", "run", "dev"]

# Build stage
FROM dev-deps AS builder
WORKDIR /app

# Set Next.js telemetry to disabled
ENV NEXT_TELEMETRY_DISABLED=1

# Copy source files
COPY . .

# Build application
RUN npm run build

# Production stage
FROM base AS production

WORKDIR /app

# Set production environment variables
ENV NODE_ENV=development \
    NEXT_TELEMETRY_DISABLED=1 \
    PORT=3000 \
    HOSTNAME="0.0.0.0"

# Create non-root user for security
RUN addgroup --system --gid 1001 nodejs && \
    adduser --system --uid 1001 nextjs

# Copy production dependencies
COPY --from=prod-deps /app/node_modules ./node_modules
COPY --from=builder /app/package.json ./package.json

# Copy only necessary files from build
COPY --from=builder /app/public ./public
COPY --from=builder /app/.next/standalone ./
COPY --from=builder /app/.next/static ./.next/static

# Set correct permissions
RUN chown -R nextjs:nodejs /app

# Switch to non-root user
USER nextjs

# Expose port
EXPOSE 3000

# Start the application
CMD ["node", "server.js"]

# Default target stage
FROM ${NODE_ENV:-development}
