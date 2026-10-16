# YZA Service Personnel API Contract

## Technician availability

`GET /pelanggan/booking-servis/technicians`

Authenticated active member only. Parameters: `date=YYYY-MM-DD`, `time=HH:MM`, and `jenis_servis_id` for an active service type.

Response contains only public/operational fields:

```json
{"date":"2026-10-13","time":"10:00","jenis_servis_id":1,"technicians":[{"id":2,"display_name":"Andi Pratama","photo":null,"title":"Senior Technician","specialization":["Engine"],"experience_years":5,"availability_status":"available","available":true}]}
```

`availability_status` is `available`, `assigned`, `time_off`, or `unavailable`. Eligibility requires active `petugas`, technician capability, bookable flag, working hours covering the complete service duration, no time off, and no overlapping active assigned Work Order. Workshop capacity remains governed by `ServiceSchedulingService`; personnel availability does not replace it.

The endpoint does not expose private phone/email, salary, login credentials, internal notes, customer names, plates, booking codes, or other assignments.

## Preference and assignment

Booking may send nullable `id_preferred_petugas`. The server accepts it only when the staff member is active, technician-capable, bookable, and eligible for the requested interval. It is a customer preference, not a promise and not the final assignment.

Final technician assignment is stored in existing `servis.id_petugas` and is selected by YZA/admin. Service advisor assignment is stored separately in nullable `servis.id_service_advisor`; members cannot set it. Both assignments are validated server-side. Existing bookings without preference and existing Work Orders retain their values.
