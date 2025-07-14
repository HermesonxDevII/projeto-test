# 💬 Melis Chat

Este projeto é um módulo de chat em tempo real desenvolvido com Laravel 12.5 + Vite, utilizando a biblioteca **WireChat** para troca de mensagens.

---

## ⚙️ Estrutura do Projeto

O **Melis Chat** não realiza autenticação diretamente. Ele funciona como um serviço incorporado via iframe no projeto principal **Melis Education**. O login e o controle de sessão são realizados no **Melis Education**, e o chat acessa essas sessões automaticamente, utilizando os dados do mesmo banco.

### Funcionalidades principais:

- Autenticação compartilhada via sessão com Melis Education.
- Comunicação com os dados do banco do Melis Education: estudantes, professores, turmas e responsáveis.
- Suporte a múltiplos tipos de usuários com o helper `getAuthenticatedParticipant()` que permite autenticar tanto usuários (`User`) quanto estudantes (`Student`).
- Views organizadas:
  - **Back-end:** `app/Livewire`
  - **Front-end:** `resources/views/vendor`

---

## 🐳 Docker

O projeto utiliza Docker, e para que Melis Chat se comunique com o banco do **Melis Education** (no ambiente local, os outros ambientes já estão configurados), é necessário adicionar uma rede Docker compartilhada.

### 1. Crie a rede compartilhada:

```bash
docker network create --driver bridge chat-shared
```

### 2. No docker-compose.yml do Melis Education, adicione a rede chat-shared

```yaml
networks:
  smartlead:
    driver: bridge
  chat-shared:
    external: true
```

### Inclua também a rede nos serviços como app e mysql:

```yaml

services:
  app:
    ...
    networks:
      - smartlead
      - chat-shared

  mysql:
    ...
    networks:
      - smartlead
      - chat-shared
```

### 3. No docker-compose.yml do Melis Chat, use a mesma configuração de rede:

```yaml
networks:
  smartlead:
    driver: bridge
  chat-shared:
    external: true
```

### Inclua também a rede nos serviços como app e mysql:

```yaml

services:
  app:
    ...
    networks:
      - smartlead
      - chat-shared

  queue:
    ...
    networks:
      - smartlead
      - chat-shared
  reverb:
    ...
    networks:
      - smartlead
      - chat-shared
```

## 🧪 Ambiente de Desenvolvimento

### Durante o desenvolvimento, use:

```bash
npm run dev
```

### Ao rodar ```docker compose up -d --build``` você poderá acessar o projeto em ``` localhost:81/```

### IMPORTANTE! Rode o projeto melis-education primeiro, para voce poder ter acesso ao banco de dados dele, e logo em seguida rode o melis chat. 

### Isso permite hot reload sem necessidade de build. Porém, em ambientes como homologação e produção, é necessário fazer o build para refletir alterações no JS/CSS.


## 🧠 Echo + Reverb + Vite

### Este projeto usa:
- Laravel Echo + Reverb para WebSockets
- Vite para build de assets (JS/CSS)

### ⚠️ Importante: As variáveis VITE_* do .env são lidas no momento do build. Ou seja, o JS final é gerado com base nas variáveis definidas no .env local no momento em que você executa npm run build.

### Exemplo de configuração do echo.js (produção e homologação):

```js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    wsPath: '/app',
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    withCredentials: true,
});
```

### Exemplo local:

```js
import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    //wsPath: '/app',
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    //disableStats: true,
    enabledTransports: ['ws', 'wss'],
    //authEndpoint: '/broadcasting/auth',
    //withCredentials: true,
});

```

## 🌍 Variáveis de Ambiente por Ambiente

### .env local (desenvolvimento):

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:gHwvyxU/31jnbraFMvq2gAeetVAqDkmNqz26PoeB/Mg=
APP_DEBUG=true
APP_URL=http://localhost:81

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

HASH_DRIVER=bcrypt

DB_CONNECTION=mysql
DB_HOST=app-mysql
DB_PORT=3306
DB_DATABASE=melis
DB_USERNAME=melis
DB_PASSWORD=melis

SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_PATH=/
SESSION_DOMAIN=localhost
SESSION_COOKIE=laravel_session_shared


BROADCAST_CONNECTION=reverb
BROADCAST_DRIVER=reverb
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

REVERB_APP_ID=my-chat-app
REVERB_APP_KEY=super-key-chat-secret
REVERB_APP_SECRET=super-secret-key-app
# As primeiras especificam o host e a porta nos quais o servidor Reverb será executado  
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8180
# enquanto o segundo par instrui o Laravel sobre para onde enviar as mensagens de broadcast.  
REVERB_HOST=reverb
REVERB_PORT=8180
REVERB_SCHEME=http

#homolog
#VITE_REVERB_APP_KEY=super-key-chat-secret
#VITE_REVERB_HOST=chat-homolog.academy-meliseducation.com
#VITE_REVERB_PORT=443
#VITE_REVERB_SCHEME=https

#producao
#VITE_REVERB_APP_KEY=super-key-chat-secret
#VITE_REVERB_HOST=chat.academy-meliseducation.com
#VITE_REVERB_PORT=443
#VITE_REVERB_SCHEME=https

#local
VITE_REVERB_APP_KEY=super-key-chat-secret
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=6001
VITE_REVERB_SCHEME=http
```

### .env homologacao
```env
VITE_REVERB_APP_KEY=super-key-chat-secret
VITE_REVERB_HOST=chat-homolog.academy-meliseducation.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https

```

### .env produção:
```env
VITE_REVERB_APP_KEY=super-key-chat-secret
VITE_REVERB_HOST=chat.academy-meliseducation.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https

```

## 📦 Processo de Build por Ambiente

### Como cada ambiente tem variáveis distintas, cada build precisa ser feito localmente com o .env correspondente ao ambiente desejado:

```bash
npm run build
```

### Depois, commite os arquivos gerados pelo Vite (public/build) e envie para o ambiente desejado.

## 🧠 Important: cherry-pick para produção

### Em vez de subir todos os commits da homologação para produção, utilize git cherry-pick para levar apenas os commits relevantes. Isso é útil, pois cada ambiente precisa de um build específico com .env diferente.

## ✅ Verificação do Build

### Após o build, você pode verificar no final do arquivo JS minificado se o wsHost está correto:

### Exemplo de build para produção:

```js
window.Echo=new Ho({broadcaster:"reverb",key:"super-key-chat-secret",wsHost:"chat.academy-meliseducation.com",wsPort:"443",wssPort:"443",wsPath:"/app",forceTLS:!0,disableStats:!0,enabledTransports:["ws","wss"],authEndpoint:"/broadcasting/auth",withCredentials:!0})
```

### Perceba que no exemplo acima são todas as variaveis utilizados no echo.js e os valores vem do .env

### Exemplo de build para homologação:

```js
wsHost:"chat-homolog.academy-meliseducation.com"
```

### Isso confirma que o JS foi gerado com os dados corretos do .env.

## ⚠️ Adendo Importante: Builds por Ambiente

### Como o projeto utiliza o Vite, que incorpora variáveis de ambiente (VITE_) diretamente no momento do build, cada ambiente (local, homologação, produção) precisa ter seu próprio build gerado de forma separada.

### Exemplo:

#### Se for subir uma nova funcionalidade visual ou interativa (ex: lightbox, comportamento de mensagens, ajustes de layout):

1. Configure o .env do ambiente desejado (ex: .env.homolog)
2. Rode o build com npm run build
3. Faça commit dos arquivos do build (public/build)
4. Suba para a branch correta do ambiente
5. Em produção: utilize git cherry-pick para pegar apenas os commits necessários da homologação, e então rode o build com o .env de produção
