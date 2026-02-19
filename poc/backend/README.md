# DCIM PoC Backend

Node.js/TypeScript API with WebSocket for real-time data streaming.

## Setup

```bash
npm install
```

## Development

```bash
npm run dev
```

## Build

```bash
npm run build
npm start
```

## Environment Variables

Copy `.env.example` to `.env` and configure:

- `PORT` - HTTP server port (default: 3000)
- `WS_PORT` - WebSocket server port (default: 3001)
- `DB_HOST` - MySQL host
- `DB_PORT` - MySQL port
- `DB_USER` - MySQL username
- `DB_PASSWORD` - MySQL password
- `DB_NAME` - MySQL database name
- `JWT_SECRET` - JWT signing secret

## API Endpoints

### Auth
- `POST /api/auth/login` - Login (username/password)
- `POST /api/auth/verify` - Verify JWT token

### Cabinets
- `GET /api/cabinets` - List all cabinets
- `GET /api/cabinets/:id` - Get cabinet by ID

### Metrics
- `GET /api/metrics/power` - Get power metrics (optional: ?cabinetId=&limit=)
- `GET /api/metrics/power/latest` - Get latest metrics for all cabinets

### WebSocket
- `ws://localhost:3001` - Real-time sensor data stream

## Mock Users

- admin / password
- operator / password
- viewer / password