FROM node:20-alpine AS deps
WORKDIR /app
COPY package.json ./
RUN npm update
# Stage 2: Build với Server Components
FROM node:20-alpine AS builder
WORKDIR /app

# Copy dependencies từ deps stage
COPY --from=deps /app/node_modules ./node_modules
COPY package.json package-lock.json* ./

# Copy source code
COPY . .

ENV NEXT_TELEMETRY_DISABLED=1
ENV NODE_ENV=production

# Build sẽ compile cả Server Components và Client Components
RUN npm run build

# Stage 3: Production runner
FROM node:20-alpine AS runner
WORKDIR /app

ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1
ENV PORT=3000
ENV HOSTNAME="0.0.0.0"

RUN addgroup --system --gid 1001 nodejs && \
    adduser --system --uid 1001 nextjs && \
    apk add --no-cache curl

# Copy standalone build (bao gồm Server Components runtime)
COPY --from=builder --chown=nextjs:nodejs /app/public ./public
COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static

USER nextjs

EXPOSE 3000

# Node.js sẽ chạy Next.js server với đầy đủ Server Components support
# Trong standalone build, file server.js nằm trong thư mục root
CMD ["node", "server.js"]
