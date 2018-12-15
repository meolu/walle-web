# STEP 1: Build
FROM node:9 as builder

WORKDIR /usr/src/app

COPY package.json  ./
RUN npm config set registry https://registry.npm.taobao.org  && npm i
COPY . .

RUN npm run build

# STEP 2: Setup
FROM nginx

ENV NGINX_PORT 8000

RUN rm -rf /usr/share/nginx/html/*
COPY --from=builder /usr/src/app/avatar /usr/share/nginx/html
COPY --from=builder /usr/src/app/public /usr/share/nginx/html
COPY --from=builder /usr/src/app/static /usr/share/nginx/html
COPY --from=builder /usr/src/app/favicon.ico /usr/share/nginx/html
COPY --from=builder /usr/src/app/index.html /usr/share/nginx/html
COPY --from=builder /usr/src/app/socket.io.min.js /usr/share/nginx/html

CMD [ "nginx", "-g", "daemon off;"]
