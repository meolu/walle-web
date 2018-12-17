# 看不懂的学习一下docker的多阶段构建
# STEP 1: Build
FROM node:9 as builder

WORKDIR /usr/src/app

COPY package.json  ./
RUN npm config set registry https://registry.npm.taobao.org  && npm i
COPY . .

# todo 谁来把这里调试通过一下
RUN npm run build

# STEP 2: Setup
FROM nginx

ENV NGINX_PORT 8000

RUN rm -rf /usr/share/nginx/html/*
#COPY --from=builder /usr/src/app/avatar /usr/share/nginx/html
#COPY --from=builder /usr/src/app/public /usr/share/nginx/html
#COPY --from=builder /usr/src/app/static /usr/share/nginx/html
#COPY --from=builder /usr/src/app/favicon.ico /usr/share/nginx/html
#COPY --from=builder /usr/src/app/index.html /usr/share/nginx/html
#COPY --from=builder /usr/src/app/socket.io.min.js /usr/share/nginx/html

# todo 上边到逻辑走不通，先直接复制目录
COPY . .

CMD [ "nginx", "-g", "daemon off;"]
