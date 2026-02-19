# DCIM Revival - Proof of Concept

A modern DCIM (Data Center Infrastructure Management) proof-of-concept demonstrating real-time power monitoring with Node.js backend and Vue.js frontend.

## Architecture

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   Vue.js        │────▶│   Node.js API   │────▶│   MySQL         │
│   Dashboard     │◄────│   + WebSocket   │◄────│   Database      │
│   (Port 5173)   │ WS  │   (Port 3000/1) │     │   (Port 3306)   │
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

## Components

### Backend (`poc/backend/`)
- **Express.js** REST API with TypeScript
- **WebSocket** server for real-time data streaming
- **JWT authentication** middleware
- **MySQL** integration for data persistence
- **Simulated sensor data** generation

### Frontend (`poc/frontend/`)
- **Vue 3** with Composition API
- **Vite** build tool
- **ECharts** for real-time visualization
- **WebSocket client** for live updates

## Quick Start

### Prerequisites
- Docker & Docker Compose
- Node.js 20+ (for local development)

### Running with Docker

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

### Access the Application

| Service | URL | Description |
|---------|-----|-------------|
| Dashboard | http://localhost:5173 | Vue.js frontend |
| API | http://localhost:3000 | REST API |
| WebSocket | ws://localhost:3001 | Real-time data |

### API Endpoints

```
GET  /health              - Health check
POST /api/auth/login      - Login (admin/password)
GET  /api/cabinets        - List cabinets
GET  /api/cabinets/:id    - Get cabinet details
GET  /api/metrics/power   - Power metrics
GET  /api/metrics/power/latest - Latest readings
WS   ws://localhost:3001  - Real-time stream
```

## Project Structure

```
dcim-revival/poc/
├── backend/
│   ├── src/
│   │   ├── index.ts           # Entry point
│   │   ├── routes/            # API routes
│   │   ├── middleware/        # Auth & error handling
│   │   ├── services/          # DB & WebSocket
│   │   └── types/             # TypeScript types
│   ├── package.json
│   └── tsconfig.json
├── frontend/
│   ├── src/
│   │   ├── main.ts            # Entry point
│   │   ├── App.vue            # Root component
│   │   ├── views/             # Page components
│   │   ├── components/        # Reusable components
│   │   ├── composables/       # Vue composables
│   │   └── utils/             # API client
│   ├── package.json
│   └── vite.config.ts
└── README.md
```

## Features

- ✅ Real-time power consumption monitoring
- ✅ Live temperature and voltage tracking
- ✅ Cabinet management with capacity visualization
- ✅ Interactive charts with ECharts
- ✅ JWT-based authentication
- ✅ Responsive dashboard UI
- ✅ WebSocket auto-reconnection
- ✅ Docker containerization

## Development

### Backend (Local)

```bash
cd poc/backend
cp .env.example .env
npm install
npm run dev
```

### Frontend (Local)

```bash
cd poc/frontend
cp .env.example .env
npm install
npm run dev
```

## Mock Credentials

| Username | Password | Role |
|----------|----------|------|
| admin | password | Admin |
| operator | password | Operator |
| viewer | password | Viewer |

## Next Steps

- [ ] Add historical data charts
- [ ] Implement alarm thresholds
- [ ] Add user management UI
- [ ] Create cabinet detail view
- [ ] Add data export functionality
- [ ] Implement PUE calculations