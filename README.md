# AirArabia Flight Integration – Symfony 7

This project provides a scalable and extensible flight integration layer for AirArabia. It supports both flight search (REST) and pricing (SOAP) operations.

---

## Technologies

- PHP 8.3
- Symfony 7
- Redis
- Docker
- PSR-4 Autoloading
- SOAP (via `\SoapClient`)
- REST (via Symfony HttpClient)
- JSON DTO-based request/response structure
- PSR-6 Cache (Symfony CachePoolInterface)

---

## API Endpoints

### 1. Search Flights (REST)

Searches available AirArabia flights.

- **URL:** `POST /api/flights/search`
- **Request:**

```json
{
  "provider": "airarabia",
  "origin": { "code": "SHJ" },
  "destination": { "code": "COK" },
  "departureDate": "2025-07-15",
  "pax": {
    "adt": 1,
    "chd": 0,
    "inf": 0
  },
  "cabinClass": "Y"
}
```

- **Response:**
```json
{
  "searchId": "cache-key-uuid",
  "flights": [
    {
      "id": "flight-1",
      "segments": [
        {
          "origin": "SHJ",
          "destination": "COK",
          "departureTime": "2025-07-15T18:00:00",
          "arrivalTime": "2025-07-15T23:25:00",
          "flightNumber": "G92801",
          "airlineCode": "G9"
        }
      ]
    }
  ]
}
```

---

### 2. Get Price (SOAP)

Fetches final pricing for a selected flight, using AirArabia’s SOAP pricing API.

- **URL:** `POST /api/flights/price`
- **Request:**

```json
{
  "provider": "airarabia",
  "searchId": "cache-key-uuid",
  "flightNumber": "G92801"
}
```

- **Response:**
```json
{
  "flightId": "flight-1",
  "currency": "AED",
  "totalPrice": 1164.15,
  "bundles": []
}
```

 **Note:** A 10% commission is applied to the total price in the backend.

---

##️ Configuration

`.env` or Symfony parameters:
```env
AIRARABIA_SOAP_WSDL=https://g914.isaaviations.com/webservices/services/AAResWebServices?wsdl
AIRARABIA_USERNAME=...
AIRARABIA_PASSWORD=...
```

---

## Testing

Use tools like Postman or `curl` to test the endpoints:

```bash
curl -X POST http://localhost:8080/api/flights/search \
  -H "Content-Type: application/json" \
  -d '{
    "provider": "airarabia",
    "origin": {"code": "SHJ"},
    "destination": {"code": "COK"},
    "departureDate": "2025-07-15",
    "pax": { "adt": 1 },
    "cabinClass": "Y"
}'
```

---

## Extensibility

The system is built using the **Strategy** and **Factory** patterns:

- New providers can be added by implementing:
  - `FlightSearchHandlerInterface`
  - `FlightPriceHandlerInterface`
- Then register the handler in `FlightHandlerFactory`.

---

## Project Structure

```
src/
├── Controller/
├── Application/
│   └── Handler/
├── Infrastructure/
│   └── Client/
│       └── AirArabia/
├── DTO/
│   ├── Api/
│   └── External/
```

---

## TODOs

- [ ] Implement DTO-based response mapping from SOAP XML
- [ ] Add support for bundled services in pricing response
- [ ] Unit tests and integration tests
- [ ] Add multi-provider support

---

