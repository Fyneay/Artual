#development
FROM node:20.16.0-alpine as prod
LABEL authors="ES"
WORKDIR /opt/server/
RUN apk add --no-cache tzdata #Установка пакета timezone
RUN apk add --no-cache git #Нужна для работы degit
#RUN corepack enable && \
#  pnpm install --frozen-lockfile && \
#  pnpm build && \
#  pnpm prune --prod
ENV TZ Europe/Moscow
ENV NODE_PATH /opt/server/node_modules
COPY /*.json ./
EXPOSE 3000
CMD ["node"]
