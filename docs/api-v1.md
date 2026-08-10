# POS Optik Melati API v1

Base URL: `/api/v1`

## Authentication

### POST `/auth/login`
Request:
```json
{
  "email": "admin@example.com",
  "password": "secret123",
  "device_name": "android-mobile"
}
```

Response:
```json
{
  "success": true,
  "token_type": "Bearer",
  "token": "1|xxxxx",
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@example.com",
    "role": "admin",
    "branch_id": 1
  }
}
```

### GET `/auth/me`
Header: `Authorization: Bearer <token>`

### POST `/auth/logout`
Header: `Authorization: Bearer <token>`

## Frames

### GET `/frames`
Query: `q`, `branch_id`, `sales_id`, `jenis_frame`, `stok_min`, `per_page`

### GET `/frames/{id}`

## Pasien

### GET `/pasien`
Query: `q`, `service_type`, `per_page`

### GET `/pasien/{id_pasien}`

## Penjualan

### GET `/penjualan`
Query: `q`, `branch_id`, `status`, `status_pengerjaan`, `start_date`, `end_date`, `per_page`

### GET `/penjualan/{id}`

## Search / Report / Comment

### GET `/search?q=...`
### GET `/reports/transactions`
### GET `/comments?penjualan_id=...`
### POST `/comments`

Request:
```json
{
  "penjualan_id": 123,
  "comment": "Follow up besok"
}
```