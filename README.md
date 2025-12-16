# Travel Request Service

Sistema de gerenciamento de solicitações de viagem desenvolvido com Laravel 12.0. API RESTful que permite usuários autenticados criarem, consultarem e gerenciarem pedidos de viagem, com controle de status e notificações por email.

## 📋 Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Arquitetura e Decisões Técnicas](#arquitetura-e-decisões-técnicas)
- [Requisitos](#requisitos)
- [Instalação e Configuração](#instalação-e-configuração)
- [Executando com Docker](#executando-com-docker)
- [Testes](#testes)
- [Documentação da API](#documentação-da-api)
- [Estrutura do Projeto](#estrutura-do-projeto)

## 🎯 Sobre o Projeto

API RESTful para gerenciamento de solicitações de viagem com as seguintes funcionalidades:

- **Autenticação JWT**: registro, login, logout e refresh token
- **Pedidos de Viagem**: criação, consulta e listagem com filtros avançados
- **Controle de Status**: transições controladas (solicitado → aprovado/cancelado)
- **Notificações**: envio automático de emails quando status muda para aprovado/cancelado
- **Administração**: sistema de permissões com roles de admin

## 🛠 Tecnologias Utilizadas

- **PHP 8.2+** e **Laravel 12.0**
- **MySQL 8** - Banco de dados
- **Redis** - Cache e filas
- **JWT Auth (tymon/jwt-auth)** - Autenticação via tokens
- **Docker & Docker Compose** - Containerização
- **Nginx** - Servidor web
- **PHPUnit** - Testes automatizados
- **MailHog** - Servidor SMTP para desenvolvimento

## 🏗 Arquitetura e Decisões Técnicas

### Padrões Arquiteturais

#### Repository Pattern
- Abstração da camada de acesso a dados através de interfaces (`TravelOrderRepositoryInterface`), facilitando testes e permitindo troca de implementação sem afetar o Service Layer.

#### Service Layer
- Encapsula toda a lógica de negócio (`TravelOrderService`), mantendo controllers leves e focados apenas em HTTP. Inclui validações de regras de negócio e orquestração de operações complexas.

#### Resource Layer
- Formatação consistente de respostas JSON através de Resources (`TravelOrderResource`, `AuthResource`), garantindo controle total sobre o formato de saída.

#### Form Request Validation
- Validação centralizada através de Form Requests customizados (`BaseFormRequest`), com mensagens de erro personalizadas e validações específicas por endpoint.

#### Enum para Status
- Uso de Enum PHP 8.1+ (`TravelOrderStatus`) com método `canUpdateTo()` para validação de transições válidas, garantindo type-safety.

#### Exceções Customizadas
- Tratamento granular de erros com exceções específicas (`NotFoundException`, `InvalidStatusTransitionException`, `InvalidTravelDatesException`) e códigos HTTP apropriados.

#### Middleware Customizado
- Middleware `EnsureUserIsAdmin` para verificação de permissões, reutilizável em múltiplas rotas.

#### Notificações Assíncronas
- Envio de emails via filas (Redis) para processamento em background, garantindo resposta HTTP rápida e retry automático.

### Estrutura de Pastas

```
app/
├── Constants/          # Constantes (mensagens)
├── Contracts/          # Interfaces (Repository Pattern)
├── Enums/              # Enumeradores (Status)
├── Exceptions/         # Exceções customizadas
├── Http/
│   ├── Controllers/    # Controllers HTTP
│   ├── Middleware/     # Middlewares customizados
│   ├── Requests/       # Form Requests (validação)
│   └── Resources/      # API Resources (formatação)
├── Models/             # Modelos Eloquent
├── Notifications/      # Notificações (emails)
├── Repositories/       # Implementação dos Repositories
└── Services/           # Service Layer (lógica de negócio)
```

## 📦 Requisitos

- Docker e Docker Compose

## 🚀 Instalação e Configuração

### Pré-requisitos

- [Docker](https://www.docker.com/get-started) e Docker Compose instalados

### Passos de Instalação

1. **Clonar o repositório**:
   ```bash
   git clone https://github.com/MatheusPrb/travel-request-service.git
   cd travel-request-service
   ```

2. **Copiar arquivo de ambiente**:
   ```bash
   cp .env.example .env
   ```

3. **Configurar variáveis de ambiente** no arquivo `.env`:
   ```env
   APP_NAME="Travel Request Service"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8080

   # --------------------
   # DATABASE
   # --------------------
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=db
   DB_USERNAME=admin
   DB_PASSWORD=admin

   # --------------------
   # MAIL (MAILHOG)
   # --------------------
   MAIL_MAILER=smtp
   MAIL_HOST=mailhog
   MAIL_PORT=1025
   MAIL_FROM_ADDRESS="noreply@travel-request.local"

   # --------------------
   # QUEUE / REDIS
   # --------------------
   QUEUE_CONNECTION=redis
   REDIS_HOST=redis
   REDIS_PORT=6379
   ```

## 🐳 Executando com Docker

### Setup Inicial

```bash
# 1. Construir e iniciar containers
docker compose up -d --build

# 2. Instalar dependências
docker compose exec app composer install

# 3. Gerar chaves
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret

# 4. Executar migrations
docker compose exec app php artisan migrate

# 5. (Opcional) Popular banco de dados
docker compose exec app php artisan db:seed

# 6. Iniciar worker de filas
docker compose exec app php artisan queue:work
```

### Acessando a Aplicação

- **API**: http://localhost:8080
- **PHPMyAdmin**: http://localhost:8081
- **MailHog UI**: http://localhost:8025

### Scripts Auxiliares

```bash
./run-migrate.sh      # Executar migrations
./run-refresh.sh      # Refresh migrations (drop e recriar)
./run-seed.sh         # Executar seeders
./run-test.sh         # Executar testes
./run-queue.sh        # Executar fila
```

## 🧪 Testes

O projeto possui testes automatizados usando PHPUnit, cobrindo autenticação JWT, CRUD de pedidos, validações, middleware de admin, notificações e tratamento de exceções.

### Executando Testes

```bash
docker compose exec app php artisan test
# ou
./run-test.sh
```

## 📚 Documentação da API

A documentação completa está disponível em formato OpenAPI/Swagger no arquivo `swagger.yaml`.

### Visualizar Documentação

**Swagger Editor Online**:
1. Acesse https://editor.swagger.io/
2. Importe o arquivo `swagger.yaml`

**Swagger UI Local**:
```bash
docker run -p 8082:8080 -e SWAGGER_JSON=/swagger.yaml -v $(pwd)/swagger.yaml:/swagger.yaml swaggerapi/swagger-ui
```
Acesse http://localhost:8082

### Importar Coleções

#### Insomnia

1. Instale: https://insomnia.rest/download
2. Importe: "Create" → "Import/Export" → "Import Data" → "From File" → selecione `Insomnia.yaml` ou `insomnia.json`
3. Variáveis pré-configuradas:
   - `base_url`: http://localhost:8080
   - `token`: preenchido após login
   - `travel_order_id`: preenchido ao criar pedido
   ---
   > **⚠️ Atenção**: Caso alguma variável não funcione corretamente após a importação, você pode configurá-la manualmente em "Manage Environments" → "Base Environment". Certifique-se de que as variáveis estão definidas corretamente antes de executar as requisições.

#### Postman

1. Instale: https://www.postman.com/downloads/
2. Importe: "Import" → selecione `postman.json`
3. Variáveis pré-configuradas (automaticamente atualizadas):
   - `base_url`: http://localhost:8080
   - `token`: salvo automaticamente após login/refresh
   - `travel_order_id`: salvo automaticamente ao criar pedido

**Nota**: Execute primeiro a requisição "Login" para obter o token automaticamente. As rotas protegidas já estão configuradas para usar o token.

## 📁 Estrutura do Projeto

```
travel-request-service/
├── app/
│   ├── Constants/              # Constantes (mensagens)
│   ├── Contracts/              # Interfaces (Repository)
│   ├── Enums/                  # Enumeradores
│   ├── Exceptions/             # Exceções customizadas
│   ├── Http/
│   │   ├── Controllers/        # Controllers
│   │   ├── Middleware/         # Middlewares
│   │   ├── Requests/           # Form Requests
│   │   └── Resources/          # API Resources
│   ├── Models/                 # Modelos Eloquent
│   ├── Notifications/          # Notificações
│   ├── Repositories/           # Repositories
│   └── Services/               # Services
├── config/                     # Arquivos de configuração
├── database/
│   ├── migrations/             # Migrations
│   ├── seeders/                # Seeders
│   └── factories/              # Factories
├── routes/
│   └── api.php                 # Rotas da API
├── tests/                      # Testes automatizados
├── docker-compose.yml          # Configuração Docker
├── Dockerfile                  # Imagem Docker
├── swagger.yaml                # Documentação OpenAPI
├── insomnia.json               # Coleção Insomnia
├── postman.json                # Coleção Postman
└── README.md                   # Este arquivo
```

## 🔒 Segurança e Regras de Negócio

### Autenticação
- JWT com tokens expiráveis
- Senhas hasheadas com bcrypt
- Middleware de autenticação em rotas protegidas

### Autorização
- Verificação de propriedade: usuários só veem seus próprios pedidos
- Middleware `admin` para rotas administrativas
- Endpoint para promover usuários a administradores

### Validações
- Validação de entrada em todos os endpoints
- Data de retorno deve ser igual ou posterior à data de partida
- Status inicial sempre "solicitado"

### Transições de Status
- `solicitado` → `aprovado` ✅
- `solicitado` → `cancelado` ✅
- `aprovado` → `cancelado` ❌ (não permitido)
- `cancelado` → qualquer outro ❌ (não permitido)

**Regra**: Pedidos aprovados não podem ser cancelados. Apenas administradores podem atualizar status.

### Notificações
- Envio automático quando status muda para `aprovado` ou `cancelado`
- Processamento assíncrono via filas (Redis)
- Emails capturados pelo MailHog em desenvolvimento

## 📝 Notas Importantes

- UUIDs para IDs de pedidos de viagem
- Datas armazenadas no formato `Y-m-d`
- Timezone padrão: UTC
- Emails enviados via fila para melhor performance

## 🤝 Contribuindo

1. Faça um fork do projeto
2. Crie uma branch (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT.

---

**Desenvolvido usando Laravel 12.0**
