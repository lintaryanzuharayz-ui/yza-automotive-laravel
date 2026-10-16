# YZA Service Scheduling API Contract

## Endpoint

`GET /pelanggan/booking-servis/availability`

Requires an authenticated, active member session. The endpoint is read-only and never returns another customer's identity, vehicle, plate, booking code, or booking status.

For a selected date, query parameters are:

- `date`: required `YYYY-MM-DD` date in the application timezone.
- `jenis_servis_id`: required active service type ID.

For a month calendar, send `month` instead of `date`:

- `month`: required `YYYY-MM`.
- `jenis_servis_id`: required active service type ID.

Month response:

```json
{
  "month": "2026-10",
  "timezone": "Asia/Jakarta",
  "days": [{"date":"2026-10-01","status":"available","is_operating_day":true,"available_slots":8}]
}
```

Example response:

```json
{
  "date": "2026-10-13",
  "is_operating_day": true,
  "blackout": null,
  "slots": [
    {
      "time": "08:00",
      "status": "available",
      "capacity_total": 3,
      "capacity_used": 1,
      "capacity_remaining": 2,
      "estimated_duration": 120,
      "available": true
    }
  ]
}
```

`status` is authoritative: `available`, `almost_full`, `full`, or `unavailable`. `almost_full` means one capacity unit remains; this threshold is intentionally small and can be refined later without changing the response shape. A service duration occupies every overlapping interval, not only its start time.

The complete service interval must fit inside an active operating window. A 120-minute service at 10:00 is valid, 11:00 is invalid when the 12:00-13:00 break follows, and 16:00 is invalid before a 17:00 close. A 60-minute service at 11:00 and 16:00 is valid. Break-overlapping and closing-overflow slots are returned as `unavailable` and are rejected again by booking storage.

Default operating rules are data-backed: Monday-Friday 08:00-17:00, break 12:00-13:00, 60-minute interval; Saturday and Sunday closed. The application timezone is used for same-day past-slot filtering. Blackout dates override weekly hours.

Errors: unauthenticated requests redirect to login; invalid parameters return HTTP 422 with Laravel validation errors; an inactive/nonexistent service type returns HTTP 422/404 according to the normal Laravel validation/model behavior.
