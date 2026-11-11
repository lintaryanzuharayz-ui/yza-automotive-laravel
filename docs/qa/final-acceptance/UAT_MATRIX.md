# Final Acceptance Evidence Matrix

Tanggal: 15 Oktober 2026  
Environment: `http://127.0.0.1:8000`

| ID | Role | Scenario | Expected | Actual | Status | Evidence |
|---|---|---|---|---|---|---|
| FA-STAFF-001 | Super Admin | Login dan redirect | Admin dashboard | `/admin/dashboard`, Super Admin Dashboard | PASS | Browser smoke session |
| FA-STAFF-002 | Owner | Login dan redirect | Executive dashboard | `/owner/dashboard` | PASS | Browser smoke session |
| FA-STAFF-003 | Sales | Login dan redirect | Sales dashboard | `/sales/dashboard` | PASS | Browser smoke session |
| FA-STAFF-004 | Service Advisor | Login dan redirect | Staff dashboard role | Dashboard Service Advisor | PASS | Browser smoke session |
| FA-STAFF-005 | Mechanic | Login dan redirect | Staff dashboard role | Dashboard Mechanic | PASS | Browser smoke session |
| FA-STAFF-006 | Warehouse | Login dan redirect | Staff dashboard role | Dashboard Warehouse Staff | PASS | Browser smoke session |
| FA-STAFF-007 | Finance | Login dan redirect | Staff dashboard role | Dashboard Finance | PASS | Browser smoke session |
| FA-STAFF-008 | Operational | Login dan redirect | Staff dashboard role | Dashboard Operational Staff | PASS | Browser smoke session |
| FA-STAFF-009 | QC | Login dan redirect | Staff dashboard role | Dashboard Quality Control | PASS | Browser smoke session |
| FA-AUTH-001 | Sales | Direct access Owner dashboard | 403 | 403 Forbidden | PASS | Browser smoke session |
| FA-RESP-001 | Guest | Register 375x812 | No page horizontal overflow | scrollWidth = viewport | PASS | DOM measurement |
| FA-RESP-002 | Guest | Register 390x844 | No page horizontal overflow | scrollWidth = viewport | PASS | DOM measurement + screenshot |
| FA-RESP-003 | Guest | Register 768x1024 | No page horizontal overflow | scrollWidth = viewport | PASS | DOM measurement |
| FA-RESP-004 | Guest | Landing 375/390/768 | No page horizontal overflow | scrollWidth = viewport | PASS | DOM measurement |
| FA-RESP-005 | Guest | Landing desktop 1366/1920 | No page horizontal overflow | scrollWidth = viewport | PASS | DOM measurement |
| FA-EXPORT-001 | Owner | Six report downloads | Files download and open | Automated route/type coverage PASS; direct browser file inspection not completed | MANUAL UAT REQUIRED | Automated export tests |
| FA-ZOOM-001 | All relevant roles | Chrome/Edge 100% and 67% | Layout usable | Native zoom control unavailable in automation | MANUAL UAT REQUIRED | No fabricated result |
| FA-MEMBER-001 | Member | Purchase E2E | Full browser workflow | Automated isolated workflow PASS; browser provider step not run | BLOCKED | No active member/provider staging credential |
| FA-MEMBER-002 | Member | Service E2E | Full browser workflow | Automated isolated workflow PASS; browser mutation workflow not run | BLOCKED | No isolated browser staging workflow |
| FA-MIDTRANS-001 | Finance | HTTPS callback/provider settlement | Sandbox callback verified | Mock/sandbox tests PASS; external HTTPS callback unavailable | BLOCKED | MidtransSandboxStage11ATest |
| FA-REFUND-001 | Finance/Owner | Provider refund/partial refund | Sandbox reconciliation | Internal financial adjustment tests PASS; provider refund not run | BLOCKED | No sandbox provider credential |

