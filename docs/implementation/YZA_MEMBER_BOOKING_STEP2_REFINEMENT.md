# YZA Member Booking Step 02 Refinement

Step 02 preserves the completed Step 01 schedule and keeps the confirmation handoff separate.

- Mileage is rendered as a read-only information card; it is sourced from the selected vehicle and is not submitted as editable booking data.
- Complaint and additional note are optional and include explanatory helper text and examples.
- The single-primary-service rule is explained while allowing multiple complaints or requests in the text fields.
- Technician preference remains optional. The default `Tidak Ada Preferensi` can always continue, while eligible technicians come from the existing public endpoint and unavailable records remain disabled.
- The sticky booking summary continues to reflect vehicle, service, date, arrival, duration, finish time, price, and technician preference.
- Step 03 remains review-only until the existing final booking submission action.

No database, authentication, scheduling, personnel, ownership, payment, or Work Order logic was changed. No migration or database reset was executed.

Validation includes Blade compilation, focused booking/personnel/scheduling tests, and scoped whitespace checks.
