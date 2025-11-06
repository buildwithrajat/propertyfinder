# PropertyFinder API - cURL Examples for Postman

## Authentication

### 1. Get Access Token

First, you need to get an access token using your API Key and API Secret.

```bash
curl --location 'https://atlas.propertyfinder.com/v1/auth/token' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data '{
  "apiKey": "YOUR_API_KEY",
  "apiSecret": "YOUR_API_SECRET"
}'
```

**Response:**
```json
{
  "accessToken": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expiresIn": 1800,
  "tokenType": "Bearer"
}
```

**Postman Setup:**
- Method: POST
- URL: `https://atlas.propertyfinder.com/v1/auth/token`
- Headers:
  - `Content-Type: application/json`
  - `Accept: application/json`
- Body (raw JSON):
```json
{
  "apiKey": "YOUR_API_KEY",
  "apiSecret": "YOUR_API_SECRET"
}
```

---

## Listings API

### 2. Get Listings (Basic)

```bash
curl --location 'https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

**Postman Setup:**
- Method: GET
- URL: `https://atlas.propertyfinder.com/v1/listings`
- Headers:
  - `Accept: application/json`
  - `Authorization: Bearer YOUR_ACCESS_TOKEN`
- Params:
  - `page`: 1
  - `perPage`: 50

---

### 3. Get Listings with Filters

#### Filter by Category and Type

```bash
curl --location 'https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50&filter[category]=residential&filter[type]=apartment' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

**Postman:**
- URL: `https://atlas.propertyfinder.com/v1/listings`
- Params:
  - `page`: 1
  - `perPage`: 50
  - `filter[category]`: residential
  - `filter[type]`: apartment

#### Filter by Location ID

```bash
curl --location 'https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50&filter[locationId]=379,456,789' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

**Postman:**
- Params:
  - `filter[locationId]`: 379,456,789

#### Filter by Offering Type (Rent/Sale)

```bash
curl --location 'https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50&filter[offeringType]=rent' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

---

### 4. Get Listings with Range Filters

#### Price Range

```bash
curl --location 'https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50&filter[price][from]=100000&filter[price][to]=500000' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

**Postman:**
- Params:
  - `filter[price][from]`: 100000
  - `filter[price][to]`: 500000

#### Size Range

```bash
curl --location 'https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50&filter[size][from]=1000&filter[size][to]=3000' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

---

### 5. Get Listings with Multiple Filters

```bash
curl --location 'https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50&filter[category]=residential&filter[type]=apartment&filter[offeringType]=rent&filter[bedrooms]=2,3&filter[bathrooms]=2&filter[price][from]=50000&filter[price][to]=200000&filter[locationId]=379' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

**Postman:**
- Params:
  - `page`: 1
  - `perPage`: 50
  - `filter[category]`: residential
  - `filter[type]`: apartment
  - `filter[offeringType]`: rent
  - `filter[bedrooms]`: 2,3
  - `filter[bathrooms]`: 2
  - `filter[price][from]`: 50000
  - `filter[price][to]`: 200000
  - `filter[locationId]`: 379

---

### 6. Get Listings with Sorting

```bash
curl --location 'https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50&orderBy=price&sort[price]=asc' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

**Postman:**
- Params:
  - `orderBy`: price
  - `sort[price]`: asc

---

### 7. Get Draft or Archived Listings

```bash
curl --location 'https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50&draft=true' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

```bash
curl --location 'https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50&archived=true' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

---

### 8. Get Listings by State

```bash
curl --location 'https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50&filter[state]=live' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

**Possible states:**
- `draft`
- `live`
- `takendown`
- `archived`
- `unpublished`
- `pending_approval`
- `rejected`
- `approved`
- `failed`

---

### 9. Get Listings by Listing Level

```bash
curl --location 'https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50&filter[listingLevel]=featured' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

**Possible values:**
- `featured`
- `premium`
- `standard`

---

### 10. Get Listings by IDs

```bash
curl --location 'https://atlas.propertyfinder.com/v1/listings?page=1&perPage=50&filter[ids]=LISTING_ID_1,LISTING_ID_2,LISTING_ID_3' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

---

## Locations API

### 11. Get Locations (Basic)

```bash
curl --location 'https://atlas.propertyfinder.com/v1/locations?page=1&perPage=100&search=aa' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

**Note:** Search parameter is required (minimum 2 characters)

### 12. Get Locations by ID

```bash
curl --location 'https://atlas.propertyfinder.com/v1/locations?page=1&perPage=100&search=00&filter[id]=379' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

**Postman:**
- URL: `https://atlas.propertyfinder.com/v1/locations`
- Params:
  - `page`: 1
  - `perPage`: 100
  - `search`: 00 (required, min 2 chars)
  - `filter[id]`: 379

### 13. Get Locations by Type

```bash
curl --location 'https://atlas.propertyfinder.com/v1/locations?page=1&perPage=100&search=00&filter[type]=CITY' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

**Location Types:**
- `REGION`
- `GOVERNORATE`
- `CITY`
- `TOWN`
- `VILLAGE`
- `DISTRICT`
- `STREET`
- `COMMUNITY`
- `SUBCOMMUNITY`
- `PROJECT`
- `TOWER`
- `COMPOUND`
- `AREA`
- `PROVINCE`
- `SUBDISTRICT`

---

## Postman Collection Setup

### Environment Variables

Create a Postman environment with these variables:

```
base_url: https://atlas.propertyfinder.com/v1
api_key: YOUR_API_KEY
api_secret: YOUR_API_SECRET
access_token: (will be set after authentication)
```

### Authentication Request

1. Create a POST request: `{{base_url}}/auth/token`
2. Body (JSON):
```json
{
  "apiKey": "{{api_key}}",
  "apiSecret": "{{api_secret}}"
}
```
3. Add Test Script to save token:
```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set("access_token", jsonData.accessToken);
}
```

### Using Token in Requests

For all subsequent requests, add header:
```
Authorization: Bearer {{access_token}}
```

---

## Complete Query Parameters Reference

### Listings API Parameters

| Parameter | Type | Example | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Page number (starts at 1) |
| `perPage` | integer | 50 | Items per page (max 100) |
| `draft` | boolean | true | Include draft listings |
| `archived` | boolean | true | Include archived listings |
| `isCtsEligible` | boolean | true | Get listings eligible for CTS |
| `filter[state]` | string | live | Filter by listing state |
| `filter[ids]` | string | id1,id2 | Comma separated listing IDs |
| `filter[category]` | string | residential | commercial or residential |
| `filter[type]` | string | apartment | Property type |
| `filter[offeringType]` | string | rent | rent or sale |
| `filter[locationId]` | string | 379,456 | Comma separated location IDs |
| `filter[bedrooms]` | string | 2,3 | Comma separated bedroom counts |
| `filter[bathrooms]` | string | 2,3 | Comma separated bathroom counts |
| `filter[price][from]` | number | 100000 | Minimum price |
| `filter[price][to]` | number | 500000 | Maximum price |
| `filter[size][from]` | number | 1000 | Minimum size |
| `filter[size][to]` | number | 3000 | Maximum size |
| `orderBy` | string | price | createdAt, price, or publishedAt |
| `sort[price]` | string | asc | asc or desc |

---

## Error Handling

### 401 Unauthorized
```json
{
  "detail": "access denied",
  "title": "Unauthorized",
  "type": "AUTHENTICATION"
}
```
**Solution:** Check your API credentials and ensure token is valid (expires in 30 minutes)

### 429 Too Many Requests
**Solution:** Wait before retrying. Rate limits:
- Auth endpoint: 60 requests/minute
- Other endpoints: 650 requests/minute

### 400 Bad Request
**Solution:** Check your query parameters for correct format and valid values

---

## Testing Tips

1. **Get Token First:** Always authenticate first to get a fresh token
2. **Token Expiry:** Tokens expire in 30 minutes (1800 seconds)
3. **Rate Limits:** Don't exceed 650 requests/minute for API calls
4. **Search Requirement:** Locations API requires `search` parameter (min 2 chars)
5. **Filter Combinations:** You can combine multiple filters in one request

