# Evidence inventory - YZA full system audit 2026-10-31

Snapshot lokal read-only; route inventory bukan bukti fitur selesai. Semua path relatif terhadap C:/xampp/htdocs/yza_automotive_laravel.

## Routes dan middleware

| Metode | URI | Nama | Action | Middleware |
|---|---|---|---|---|
| GET,HEAD | /up |  | Closure |  |
| GET,HEAD | / |  | Closure | web |
| GET,HEAD | /mobil | mobil.index | App\Http\Controllers\Public\MobilController@index | web |
| GET,HEAD | /koleksi/{slug} | mobil.showcase | App\Http\Controllers\Public\MobilController@showcase | web |
| GET,HEAD | /mobil/{mobil} | mobil.show | App\Http\Controllers\Public\MobilController@show | web |
| GET,HEAD | /trade-in/start | trade-in.entry | App\Http\Controllers\Public\MobilController@tradeInEntry | web |
| GET,HEAD | /trade-in/membership-entry | trade-in.membership-entry | App\Http\Controllers\Public\MobilController@membershipEntry | web |
| GET,HEAD | /login | login | App\Http\Controllers\AuthController@create | web |
| POST | /login | login.store | App\Http\Controllers\AuthController@store | web; guest |
| GET,HEAD | /register | register | App\Http\Controllers\RegistrationController@create | web; guest |
| GET,HEAD | /register/regions/{level}/{parent?} | register.regions | App\Http\Controllers\RegistrationController@regions | web; guest |
| POST | /register | register.store | App\Http\Controllers\RegistrationController@store | web; guest |
| POST | /logout | logout | App\Http\Controllers\AuthController@destroy | web; auth |
| POST | /payment/midtrans/notification | midtrans.notification | App\Http\Controllers\MidtransController@notification | web |
| GET,HEAD | /staff/dashboard | staff.dashboard | App\Http\Controllers\RoleDashboardController | web; auth; role:owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control |
| GET,HEAD | /owner/dashboard | owner.dashboard | App\Http\Controllers\OwnerDashboardController@dashboard | web; auth; role:owner,super_admin; permission:reports.view |
| GET,HEAD | /owner/penjualan | owner.sales-report | App\Http\Controllers\OwnerDashboardController@salesReport | web; auth; role:owner,super_admin; permission:reports.view |
| GET,HEAD | /owner/penjualan/export | owner.sales-export | App\Http\Controllers\OwnerDashboardController@exportSales | web; auth; role:owner,super_admin; permission:reports.view |
| GET,HEAD | /owner/servis | owner.service-report | App\Http\Controllers\OwnerDashboardController@serviceReport | web; auth; role:owner,super_admin; permission:reports.view |
| GET,HEAD | /owner/servis/export | owner.service-export | App\Http\Controllers\OwnerDashboardController@exportService | web; auth; role:owner,super_admin; permission:reports.view |
| GET,HEAD | /owner/performa-sales | owner.performance-report | App\Http\Controllers\OwnerDashboardController@salesPerformanceReport | web; auth; role:owner,super_admin; permission:reports.view |
| GET,HEAD | /owner/performa-sales/export | owner.performance-export | App\Http\Controllers\OwnerDashboardController@exportSalesPerformance | web; auth; role:owner,super_admin; permission:reports.view |
| GET,HEAD | /owner/pendapatan-pembayaran | owner.payment-report | App\Http\Controllers\OwnerDashboardController@paymentReport | web; auth; role:owner,super_admin; permission:reports.view |
| GET,HEAD | /owner/pendapatan-pembayaran/export | owner.payment-export | App\Http\Controllers\OwnerDashboardController@exportPayment | web; auth; role:owner,super_admin; permission:reports.view |
| GET,HEAD | /owner/inventori-kendaraan | owner.inventory-report | App\Http\Controllers\OwnerDashboardController@inventoryReport | web; auth; role:owner,super_admin; permission:reports.view |
| GET,HEAD | /owner/inventori-kendaraan/export | owner.inventory-export | App\Http\Controllers\OwnerDashboardController@exportInventory | web; auth; role:owner,super_admin; permission:reports.view |
| GET,HEAD | /owner/sales-awards | owner.sales-awards | App\Http\Controllers\OwnerDashboardController@salesAwards | web; auth; role:owner,super_admin; permission:reports.view |
| GET,HEAD | /owner/sales-awards/export | owner.sales-awards-export | App\Http\Controllers\OwnerDashboardController@exportSalesAwards | web; auth; role:owner,super_admin; permission:reports.view |
| POST | /owner/sales-awards/finalize | owner.sales-awards-finalize | App\Http\Controllers\OwnerDashboardController@finalizeSalesAwards | web; auth; role:owner,super_admin; permission:reports.view; role:super_admin |
| GET,HEAD | /sales/dashboard | sales.dashboard | App\Http\Controllers\SalesManagementController@dashboard | web; auth; role:sales,super_admin; permission:sales.view |
| GET,HEAD | /sales/awards | sales.awards | App\Http\Controllers\SalesManagementController@awards | web; auth; role:sales,super_admin; permission:sales.view |
| GET,HEAD | /sales/prospek | sales.leads.index | App\Http\Controllers\SalesManagementController@leads | web; auth; role:sales,super_admin; permission:sales.view |
| GET,HEAD | /sales/prospek/create | sales.leads.create | App\Http\Controllers\SalesManagementController@createLead | web; auth; role:sales,super_admin; permission:sales.view; permission:sales.manage |
| POST | /sales/prospek | sales.leads.store | App\Http\Controllers\SalesManagementController@storeLead | web; auth; role:sales,super_admin; permission:sales.view; permission:sales.manage |
| GET,HEAD | /sales/prospek/{lead} | sales.leads.show | App\Http\Controllers\SalesManagementController@showLead | web; auth; role:sales,super_admin; permission:sales.view |
| POST | /sales/prospek/{lead}/activity | sales.leads.activity | App\Http\Controllers\SalesManagementController@activity | web; auth; role:sales,super_admin; permission:sales.view; permission:sales.manage |
| PUT | /sales/prospek/{lead}/assignment | sales.leads.assignment | App\Http\Controllers\SalesManagementController@assignLead | web; auth; role:sales,super_admin; permission:sales.view; role:super_admin |
| GET,HEAD | /sales/test-drive | sales.test-drives.index | App\Http\Controllers\SalesManagementController@testDrives | web; auth; role:sales,super_admin; permission:sales.view |
| PUT | /sales/test-drive/{testDrive} | sales.test-drives.update | App\Http\Controllers\SalesManagementController@updateTestDrive | web; auth; role:sales,super_admin; permission:sales.view; permission:sales.manage |
| PUT | /sales/test-drive/{testDrive}/assignment | sales.test-drives.assignment | App\Http\Controllers\SalesManagementController@assignTestDrive | web; auth; role:sales,super_admin; permission:sales.view; role:super_admin |
| GET,HEAD | /sales/pesanan | sales.orders.index | App\Http\Controllers\SalesManagementController@orders | web; auth; role:sales,super_admin; permission:sales.view |
| PUT | /sales/pesanan/{order}/assignment | sales.orders.assignment | App\Http\Controllers\SalesManagementController@assignOrder | web; auth; role:sales,super_admin; permission:sales.view; role:super_admin |
| GET,HEAD | /sales/penjualan | sales.sales.index | App\Http\Controllers\SalesManagementController@sales | web; auth; role:sales,super_admin; permission:sales.view |
| PUT | /sales/penjualan/{sale}/assignment | sales.sales.assignment | App\Http\Controllers\SalesManagementController@assignSale | web; auth; role:sales,super_admin; permission:sales.view; role:super_admin |
| GET,HEAD | /sales/target | sales.targets.index | App\Http\Controllers\SalesManagementController@targets | web; auth; role:sales,super_admin; permission:sales.view |
| POST | /sales/target | sales.targets.store | App\Http\Controllers\SalesManagementController@storeTarget | web; auth; role:sales,super_admin; permission:sales.view; role:super_admin |
| GET,HEAD | /promo-preview | pelanggan.promo.preview.list | App\Http\Controllers\Pelanggan\PromoPreviewController@index | web; auth |
| GET,HEAD | /promo-preview/calculate | pelanggan.promo.preview | App\Http\Controllers\Pelanggan\PromoPreviewController@preview | web; auth |
| GET,HEAD | /admin/dashboard | admin.dashboard | App\Http\Controllers\DashboardController@admin | web; auth; role:admin,super_admin |
| GET,HEAD | /petugas/dashboard | petugas.dashboard | App\Http\Controllers\DashboardController@petugas | web; auth; role:petugas |
| GET,HEAD | /pelanggan/dashboard | pelanggan.dashboard | App\Http\Controllers\DashboardController@pelanggan | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/member | pelanggan.member | App\Http\Controllers\DashboardController@pelanggan | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/profil | pelanggan.profile | App\Http\Controllers\ProfileController@pelanggan | web; auth; role:pelanggan; member |
| PUT | /pelanggan/profil | pelanggan.profile.update | App\Http\Controllers\ProfileController@updatePelanggan | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/membership/aktivasi | pelanggan.membership.activation | App\Http\Controllers\MembershipController@show | web; auth; role:pelanggan |
| GET,HEAD | /pelanggan/membership/finish | pelanggan.membership.finish | App\Http\Controllers\MembershipController@finish | web; auth; role:pelanggan |
| GET,HEAD | /petugas/profil | petugas.profile | App\Http\Controllers\ProfileController@petugas | web; auth; role:petugas |
| PUT | /petugas/profil | petugas.profile.update | App\Http\Controllers\ProfileController@updatePetugas | web; auth; role:petugas |
| GET,HEAD | /akun/password | account.password | App\Http\Controllers\ProfileController@password | web; auth |
| PUT | /akun/password | account.password.update | App\Http\Controllers\ProfileController@updatePassword | web; auth |
| GET,HEAD | /notifikasi | notifications.index | App\Http\Controllers\NotifikasiController@index | web; auth |
| GET,HEAD | /notifikasi/sync | notifications.sync | App\Http\Controllers\NotifikasiController@sync | web; auth |
| PATCH | /notifikasi/read-all | notifications.read-all | App\Http\Controllers\NotifikasiController@readAll | web; auth |
| GET,HEAD | /notifikasi/{notifikasi}/open | notifications.open | App\Http\Controllers\NotifikasiController@open | web; auth |
| PATCH | /notifikasi/{notifikasi}/read | notifications.read | App\Http\Controllers\NotifikasiController@read | web; auth |
| POST | /pelanggan/membership/pay | pelanggan.membership.pay | App\Http\Controllers\MembershipController@pay | web; auth; role:pelanggan; member |
| POST | /pelanggan/membership/sync | pelanggan.membership.sync | App\Http\Controllers\MembershipController@sync | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/katalog-mobil | pelanggan.katalog-mobil.index | App\Http\Controllers\Public\MobilController@index | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/katalog-mobil/koleksi/{slug} | pelanggan.katalog-mobil.showcase | App\Http\Controllers\Public\MobilController@memberShowcase | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/katalog-mobil/{mobil} | pelanggan.katalog-mobil.show | App\Http\Controllers\Public\MobilController@memberShow | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/pesanan-mobil | pelanggan.pesanan-mobil.index | App\Http\Controllers\Pelanggan\MemberVehiclePurchaseController@index | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/pesanan-mobil/create/{mobil} | pelanggan.pesanan-mobil.create | App\Http\Controllers\Pelanggan\MemberVehiclePurchaseController@create | web; auth; role:pelanggan; member |
| POST | /pelanggan/pesanan-mobil/{mobil} | pelanggan.pesanan-mobil.store | App\Http\Controllers\Pelanggan\MemberVehiclePurchaseController@store | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/pesanan-mobil/{pesanan_mobil} | pelanggan.pesanan-mobil.show | App\Http\Controllers\Pelanggan\MemberVehiclePurchaseController@show | web; auth; role:pelanggan; member |
| POST | /pelanggan/pesanan-mobil/{pesanan_mobil}/pembayaran/buka-kembali | pelanggan.pesanan-mobil.payment.reopen | App\Http\Controllers\Pelanggan\MemberVehiclePurchaseController@reopenPayment | web; auth; role:pelanggan; member |
| POST | /pelanggan/pesanan-mobil/{pesanan_mobil}/pembayaran-offline | pelanggan.pesanan-mobil.payment.offline | App\Http\Controllers\Pelanggan\MemberVehiclePurchaseController@offlinePayment | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/pre-order | pelanggan.preorder.index | App\Http\Controllers\Pelanggan\MemberVehiclePreorderController@index | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/pre-order/create | pelanggan.preorder.create | App\Http\Controllers\Pelanggan\MemberVehiclePreorderController@create | web; auth; role:pelanggan; member |
| POST | /pelanggan/pre-order | pelanggan.preorder.store | App\Http\Controllers\Pelanggan\MemberVehiclePreorderController@store | web; auth; role:pelanggan; member |
| PATCH | /pelanggan/pre-order/{preorder}/cancel | pelanggan.preorder.cancel | App\Http\Controllers\Pelanggan\MemberVehiclePreorderController@cancel | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/kendaraan | pelanggan.kendaraan.index | App\Http\Controllers\Pelanggan\KendaraanController@index | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/kendaraan/create | pelanggan.kendaraan.create | App\Http\Controllers\Pelanggan\KendaraanController@create | web; auth; role:pelanggan; member |
| POST | /pelanggan/kendaraan | pelanggan.kendaraan.store | App\Http\Controllers\Pelanggan\KendaraanController@store | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/kendaraan/{kendaraan} | pelanggan.kendaraan.show | App\Http\Controllers\Pelanggan\KendaraanController@show | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/kendaraan/{kendaraan}/edit | pelanggan.kendaraan.edit | App\Http\Controllers\Pelanggan\KendaraanController@edit | web; auth; role:pelanggan; member |
| PUT,PATCH | /pelanggan/kendaraan/{kendaraan} | pelanggan.kendaraan.update | App\Http\Controllers\Pelanggan\KendaraanController@update | web; auth; role:pelanggan; member |
| PATCH | /pelanggan/kendaraan/{kendaraan}/nonaktifkan | pelanggan.kendaraan.deactivate | App\Http\Controllers\Pelanggan\KendaraanController@deactivate | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/booking-mobil | pelanggan.booking.index | App\Http\Controllers\Pelanggan\BookingMobilController@index | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/booking-mobil/create | pelanggan.booking.create | App\Http\Controllers\Pelanggan\BookingMobilController@create | web; auth; role:pelanggan; member |
| POST | /pelanggan/booking-mobil | pelanggan.booking.store | App\Http\Controllers\Pelanggan\BookingMobilController@store | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/booking-mobil/{booking_mobil} | pelanggan.booking.show | App\Http\Controllers\Pelanggan\BookingMobilController@show | web; auth; role:pelanggan; member |
| PATCH | /pelanggan/booking-mobil/{booking_mobil}/cancel | pelanggan.booking.cancel | App\Http\Controllers\Pelanggan\BookingMobilController@cancel | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/pengajuan-mobil | pelanggan.pengajuan-mobil.index | App\Http\Controllers\Pelanggan\PengajuanMobilController@index | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/pengajuan-mobil/create | pelanggan.pengajuan-mobil.create | App\Http\Controllers\Pelanggan\PengajuanMobilController@create | web; auth; role:pelanggan; member |
| POST | /pelanggan/pengajuan-mobil | pelanggan.pengajuan-mobil.store | App\Http\Controllers\Pelanggan\PengajuanMobilController@store | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/pengajuan-mobil/{pengajuan_mobil} | pelanggan.pengajuan-mobil.show | App\Http\Controllers\Pelanggan\PengajuanMobilController@show | web; auth; role:pelanggan; member |
| POST | /pelanggan/pengajuan-mobil/{pengajuan_mobil}/decision/{decision} | pelanggan.pengajuan-mobil.decision | App\Http\Controllers\Pelanggan\PengajuanMobilController@decision | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/trade-in | pelanggan.trade-in.index | App\Http\Controllers\Pelanggan\TradeInController@index | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/trade-in/create | pelanggan.trade-in.create | App\Http\Controllers\Pelanggan\TradeInController@create | web; auth; role:pelanggan; member; verified.owner |
| POST | /pelanggan/trade-in | pelanggan.trade-in.store | App\Http\Controllers\Pelanggan\TradeInController@store | web; auth; role:pelanggan; member; verified.owner |
| GET,HEAD | /pelanggan/trade-in/{trade_in} | pelanggan.trade-in.show | App\Http\Controllers\Pelanggan\TradeInController@show | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/invoice/trade-in/{trade_in} | pelanggan.invoice.trade-in | App\Http\Controllers\TradeInInvoiceController@show | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/test-drive | pelanggan.test-drive.index | App\Http\Controllers\Pelanggan\TestDriveController@index | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/test-drive/create | pelanggan.test-drive.create | App\Http\Controllers\Pelanggan\TestDriveController@create | web; auth; role:pelanggan; member |
| POST | /pelanggan/test-drive | pelanggan.test-drive.store | App\Http\Controllers\Pelanggan\TestDriveController@store | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/test-drive/{test_drive} | pelanggan.test-drive.show | App\Http\Controllers\Pelanggan\TestDriveController@show | web; auth; role:pelanggan; member |
| PATCH | /pelanggan/test-drive/{test_drive}/cancel | pelanggan.test-drive.cancel | App\Http\Controllers\Pelanggan\TestDriveController@cancel | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/booking-servis/availability | pelanggan.booking-servis.availability | App\Http\Controllers\Pelanggan\BookingServisController@availability | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/booking-servis/technicians | pelanggan.booking-servis.technicians | App\Http\Controllers\Pelanggan\BookingServisController@technicians | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/booking-servis | pelanggan.booking-servis.index | App\Http\Controllers\Pelanggan\BookingServisController@index | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/booking-servis/create | pelanggan.booking-servis.create | App\Http\Controllers\Pelanggan\BookingServisController@create | web; auth; role:pelanggan; member; verified.owner |
| POST | /pelanggan/booking-servis | pelanggan.booking-servis.store | App\Http\Controllers\Pelanggan\BookingServisController@store | web; auth; role:pelanggan; member; verified.owner |
| GET,HEAD | /pelanggan/booking-servis/{booking_servis} | pelanggan.booking-servis.show | App\Http\Controllers\Pelanggan\BookingServisController@show | web; auth; role:pelanggan; member |
| PATCH | /pelanggan/booking-servis/{booking_servis}/cancel | pelanggan.booking-servis.cancel | App\Http\Controllers\Pelanggan\BookingServisController@cancel | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/booking-cuci | pelanggan.booking-cuci.index | App\Http\Controllers\Pelanggan\BookingCuciController@index | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/booking-cuci/create | pelanggan.booking-cuci.create | App\Http\Controllers\Pelanggan\BookingCuciController@create | web; auth; role:pelanggan; member; verified.owner |
| POST | /pelanggan/booking-cuci | pelanggan.booking-cuci.store | App\Http\Controllers\Pelanggan\BookingCuciController@store | web; auth; role:pelanggan; member; verified.owner |
| GET,HEAD | /pelanggan/booking-cuci/{booking_cuci} | pelanggan.booking-cuci.show | App\Http\Controllers\Pelanggan\BookingCuciController@show | web; auth; role:pelanggan; member |
| PATCH | /pelanggan/booking-cuci/{booking_cuci}/cancel | pelanggan.booking-cuci.cancel | App\Http\Controllers\Pelanggan\BookingCuciController@cancel | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/servis | pelanggan.servis.index | App\Http\Controllers\Pelanggan\ServisController@index | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/servis/{servis} | pelanggan.servis.show | App\Http\Controllers\Pelanggan\ServisController@show | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/servis/{servis}/approval | pelanggan.servis.approval | App\Http\Controllers\Pelanggan\ServisController@approval | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/servis/{servis}/tracking | pelanggan.servis.tracking | App\Http\Controllers\ServiceExecutionController@tracking | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/servis/{servis}/approvals | pelanggan.servis.approvals | App\Http\Controllers\ServiceExecutionController@approvals | web; auth; role:pelanggan; member |
| POST | /pelanggan/servis/{servis}/approvals/{approval}/decision | pelanggan.servis.approvals.decision | App\Http\Controllers\ServiceExecutionController@decide | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/invoice/servis/{transaksi} | pelanggan.invoice.servis | App\Http\Controllers\InvoiceController@show | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/invoice/car-wash/{transaksi} | pelanggan.invoice.carwash | App\Http\Controllers\InvoiceController@show | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/invoice/sparepart/{transaksi} | pelanggan.invoice.sparepart | App\Http\Controllers\InvoiceController@show | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/invoice/penjualan-mobil/{transaksi} | pelanggan.invoice.car-sale | App\Http\Controllers\CarSaleInvoiceController@show | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/invoice/pembelian-mobil/{pembelian_mobil} | pelanggan.invoice.used-car | App\Http\Controllers\UsedCarReceiptController@show | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/poin | pelanggan.poin | App\Http\Controllers\Pelanggan\PointController@index | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/promo | pelanggan.promo.index | App\Http\Controllers\Pelanggan\PromoMemberController@index | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/promo/{promo_member} | pelanggan.promo.show | App\Http\Controllers\Pelanggan\PromoMemberController@show | web; auth; role:pelanggan; member |
| GET,HEAD | /pelanggan/pengingat-servis | pelanggan.pengingat-servis | App\Http\Controllers\Pelanggan\ServiceReminderController@index | web; auth; role:pelanggan; member |
| POST | /pelanggan/transaksi/{transaksi}/midtrans | pelanggan.midtrans.create | App\Http\Controllers\MidtransController@create | web; auth; role:pelanggan; member; throttle:10,1 |
| POST | /pelanggan/pembayaran/midtrans/{gateway}/sync | pelanggan.midtrans.sync | App\Http\Controllers\MidtransController@sync | web; auth; role:pelanggan; member; throttle:10,1 |
| GET,HEAD | /pelanggan/pembayaran/midtrans/kembali | pelanggan.midtrans.returned | App\Http\Controllers\MidtransController@returned | web; auth; role:pelanggan; member |
| GET,HEAD | /admin/aktivitas | admin.aktivitas.index | App\Http\Controllers\Admin\AdminWorkspaceController@activity | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/katalog-showroom | admin.katalog-showroom.index | App\Http\Controllers\Admin\AdminWorkspaceController@catalog | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/pdi | admin.pdi.index | App\Http\Controllers\Admin\AdminAuxiliaryController@ordersWorkflow | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/jadwal-pengambilan | admin.pengambilan.index | App\Http\Controllers\Admin\AdminAuxiliaryController@ordersWorkflow | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/serah-terima | admin.serah-terima.index | App\Http\Controllers\Admin\AdminAuxiliaryController@ordersWorkflow | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/transaksi | admin.transaksi.index | App\Http\Controllers\Admin\AdminAuxiliaryController@transactions | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/invoice | admin.invoices.index | App\Http\Controllers\Admin\AdminAuxiliaryController@transactions | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/garage | admin.garage.index | App\Http\Controllers\Admin\AdminAuxiliaryController@garage | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/roles-permissions | admin.roles.index | App\Http\Controllers\Admin\AdminAuxiliaryController@roles | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/audit-log | admin.audit-log.index | App\Http\Controllers\Admin\AdminAuxiliaryController@auditLog | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/audit-log/export | admin.audit-log.export | App\Http\Controllers\Admin\AdminAuxiliaryController@auditLogExport | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/keuangan | admin.finance-dashboard.index | App\Http\Controllers\Admin\PaymentManagementController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/pembayaran | admin.payments.index | App\Http\Controllers\Admin\PaymentManagementController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/financial-adjustments | admin.financial-adjustments.index | App\Http\Controllers\FinancialAdjustmentController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access; permission:finance.view |
| POST | /admin/financial-adjustments | admin.financial-adjustments.store | App\Http\Controllers\FinancialAdjustmentController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access; permission:finance.verify |
| PATCH | /admin/financial-adjustments/{adjustment}/transition | admin.financial-adjustments.transition | App\Http\Controllers\FinancialAdjustmentController@transition | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access; permission:finance.verify |
| GET,HEAD | /admin/pembayaran/{pembayaran} | admin.payments.show | App\Http\Controllers\Admin\PaymentManagementController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pembayaran/{pembayaran}/confirm | admin.payments.confirm | App\Http\Controllers\Admin\PaymentManagementController@verify | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pembayaran/{pembayaran}/reject | admin.payment.reject | App\Http\Controllers\Admin\PaymentManagementController@reject | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pembayaran-online/{gateway}/sync | admin.payments.online.sync | App\Http\Controllers\Admin\PaymentManagementController@sync | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/membership | admin.membership.index | App\Http\Controllers\Admin\MembershipController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/jenis-servis | admin.jenis-servis.index | App\Http\Controllers\Admin\JenisServisController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/jenis-servis/create | admin.jenis-servis.create | App\Http\Controllers\Admin\JenisServisController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/jenis-servis | admin.jenis-servis.store | App\Http\Controllers\Admin\JenisServisController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/jenis-servis/{jenis_servis} | admin.jenis-servis.show | App\Http\Controllers\Admin\JenisServisController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/jenis-servis/{jenis_servis}/edit | admin.jenis-servis.edit | App\Http\Controllers\Admin\JenisServisController@edit | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT,PATCH | /admin/jenis-servis/{jenis_servis} | admin.jenis-servis.update | App\Http\Controllers\Admin\JenisServisController@update | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PATCH | /admin/jenis-servis/{jenis_servis}/toggle | admin.jenis-servis.toggle | App\Http\Controllers\Admin\JenisServisController@toggle | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/paket-cuci | admin.paket-cuci.index | App\Http\Controllers\Admin\PaketCuciController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/paket-cuci/create | admin.paket-cuci.create | App\Http\Controllers\Admin\PaketCuciController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/paket-cuci | admin.paket-cuci.store | App\Http\Controllers\Admin\PaketCuciController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/paket-cuci/{paket_cuci} | admin.paket-cuci.show | App\Http\Controllers\Admin\PaketCuciController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/paket-cuci/{paket_cuci}/edit | admin.paket-cuci.edit | App\Http\Controllers\Admin\PaketCuciController@edit | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT,PATCH | /admin/paket-cuci/{paket_cuci} | admin.paket-cuci.update | App\Http\Controllers\Admin\PaketCuciController@update | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PATCH | /admin/paket-cuci/{paket_cuci}/toggle | admin.paket-cuci.toggle | App\Http\Controllers\Admin\PaketCuciController@toggle | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/promo-member/from-simulation/{promo_simulation}/create | admin.promo-member.from-simulation.create | App\Http\Controllers\Admin\PromoMemberController@createFromSimulation | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/promo-member | admin.promo-member.index | App\Http\Controllers\Admin\PromoMemberController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/promo-member/create | admin.promo-member.create | App\Http\Controllers\Admin\PromoMemberController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/promo-member | admin.promo-member.store | App\Http\Controllers\Admin\PromoMemberController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/promo-member/{promo_member} | admin.promo-member.show | App\Http\Controllers\Admin\PromoMemberController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/promo-member/{promo_member}/edit | admin.promo-member.edit | App\Http\Controllers\Admin\PromoMemberController@edit | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT,PATCH | /admin/promo-member/{promo_member} | admin.promo-member.update | App\Http\Controllers\Admin\PromoMemberController@update | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PATCH | /admin/promo-member/{promo_member}/toggle | admin.promo-member.toggle | App\Http\Controllers\Admin\PromoMemberController@toggle | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/promo-simulations | admin.promo-simulations.index | App\Http\Controllers\Admin\PromoSimulationController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/promo-simulations/create | admin.promo-simulations.create | App\Http\Controllers\Admin\PromoSimulationController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/promo-simulations | admin.promo-simulations.store | App\Http\Controllers\Admin\PromoSimulationController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/promo-simulations/{promo_simulation} | admin.promo-simulations.show | App\Http\Controllers\Admin\PromoSimulationController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/promo-analytics | admin.promo-analytics.index | App\Http\Controllers\Admin\PromoAnalyticsController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/promo-analytics/{promo_member} | admin.promo-analytics.show | App\Http\Controllers\Admin\PromoAnalyticsController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/laporan-servis | admin.laporan-servis.index | App\Http\Controllers\Admin\LaporanServisController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/pengaturan | admin.pengaturan.index | App\Http\Controllers\Admin\PengaturanController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT | /admin/pengaturan | admin.pengaturan.update-many | App\Http\Controllers\Admin\PengaturanController@updateMany | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT | /admin/pengaturan/{pengaturan} | admin.pengaturan.update | App\Http\Controllers\Admin\PengaturanController@update | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/pelanggan | admin.pelanggan.index | App\Http\Controllers\Admin\PelangganController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/pelanggan/create | admin.pelanggan.create | App\Http\Controllers\Admin\PelangganController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pelanggan | admin.pelanggan.store | App\Http\Controllers\Admin\PelangganController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/pelanggan/{pelanggan} | admin.pelanggan.show | App\Http\Controllers\Admin\PelangganController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/pelanggan/{pelanggan}/edit | admin.pelanggan.edit | App\Http\Controllers\Admin\PelangganController@edit | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT,PATCH | /admin/pelanggan/{pelanggan} | admin.pelanggan.update | App\Http\Controllers\Admin\PelangganController@update | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PATCH | /admin/pelanggan/{pelanggan}/toggle | admin.pelanggan.toggle | App\Http\Controllers\Admin\PelangganController@toggle | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/petugas | admin.petugas.index | App\Http\Controllers\Admin\PetugasController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/petugas/create | admin.petugas.create | App\Http\Controllers\Admin\PetugasController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/petugas | admin.petugas.store | App\Http\Controllers\Admin\PetugasController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/petugas/{petugas} | admin.petugas.show | App\Http\Controllers\Admin\PetugasController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/petugas/{petugas}/edit | admin.petugas.edit | App\Http\Controllers\Admin\PetugasController@edit | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT,PATCH | /admin/petugas/{petugas} | admin.petugas.update | App\Http\Controllers\Admin\PetugasController@update | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PATCH | /admin/petugas/{petugas}/toggle | admin.petugas.toggle | App\Http\Controllers\Admin\PetugasController@toggle | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/kategori-sparepart | admin.kategori-sparepart.index | App\Http\Controllers\Admin\KategoriSparepartController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/kategori-sparepart/create | admin.kategori-sparepart.create | App\Http\Controllers\Admin\KategoriSparepartController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/kategori-sparepart | admin.kategori-sparepart.store | App\Http\Controllers\Admin\KategoriSparepartController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/kategori-sparepart/{kategori_sparepart}/edit | admin.kategori-sparepart.edit | App\Http\Controllers\Admin\KategoriSparepartController@edit | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT,PATCH | /admin/kategori-sparepart/{kategori_sparepart} | admin.kategori-sparepart.update | App\Http\Controllers\Admin\KategoriSparepartController@update | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PATCH | /admin/kategori-sparepart/{kategori_sparepart}/toggle | admin.kategori-sparepart.toggle | App\Http\Controllers\Admin\KategoriSparepartController@toggle | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/supplier | admin.supplier.index | App\Http\Controllers\Admin\SupplierController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/supplier/create | admin.supplier.create | App\Http\Controllers\Admin\SupplierController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/supplier | admin.supplier.store | App\Http\Controllers\Admin\SupplierController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/supplier/{supplier} | admin.supplier.show | App\Http\Controllers\Admin\SupplierController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/supplier/{supplier}/edit | admin.supplier.edit | App\Http\Controllers\Admin\SupplierController@edit | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT,PATCH | /admin/supplier/{supplier} | admin.supplier.update | App\Http\Controllers\Admin\SupplierController@update | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PATCH | /admin/supplier/{supplier}/toggle | admin.supplier.toggle | App\Http\Controllers\Admin\SupplierController@toggle | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/sparepart | admin.sparepart.index | App\Http\Controllers\Admin\SparepartController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/sparepart/create | admin.sparepart.create | App\Http\Controllers\Admin\SparepartController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/sparepart | admin.sparepart.store | App\Http\Controllers\Admin\SparepartController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/sparepart/{sparepart} | admin.sparepart.show | App\Http\Controllers\Admin\SparepartController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/sparepart/{sparepart}/edit | admin.sparepart.edit | App\Http\Controllers\Admin\SparepartController@edit | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT,PATCH | /admin/sparepart/{sparepart} | admin.sparepart.update | App\Http\Controllers\Admin\SparepartController@update | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PATCH | /admin/sparepart/{sparepart}/toggle | admin.sparepart.toggle | App\Http\Controllers\Admin\SparepartController@toggle | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/sparepart/{sparepart}/fitment | admin.sparepart.fitment.store | App\Http\Controllers\Admin\SparepartController@storeFitment | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| DELETE | /admin/sparepart/{sparepart}/fitment/{fitment} | admin.sparepart.fitment.destroy | App\Http\Controllers\Admin\SparepartController@destroyFitment | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/inventory/stok | admin.inventory.stock | App\Http\Controllers\Admin\InventoryController@stock | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/inventory/riwayat | admin.inventory.history | App\Http\Controllers\Admin\InventoryController@history | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/inventory/penyesuaian/{sparepart?} | admin.inventory.adjustment | App\Http\Controllers\Admin\InventoryController@adjustment | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/inventory/penyesuaian | admin.inventory.adjust | App\Http\Controllers\Admin\InventoryController@adjust | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/stok-masuk | admin.stok-masuk.index | App\Http\Controllers\Admin\StokMasukController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/stok-masuk/create | admin.stok-masuk.create | App\Http\Controllers\Admin\StokMasukController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/stok-masuk | admin.stok-masuk.store | App\Http\Controllers\Admin\StokMasukController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/stok-masuk/{stok_masuk} | admin.stok-masuk.show | App\Http\Controllers\Admin\StokMasukController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/stok-masuk/{stok_masuk}/terima | admin.stok-masuk.finalize | App\Http\Controllers\Admin\StokMasukController@finalize | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/stok-masuk/{stok_masuk}/batal | admin.stok-masuk.cancel | App\Http\Controllers\Admin\StokMasukController@cancel | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/stok-masuk/{stok_masuk}/detail | admin.stok-masuk.detail.store | App\Http\Controllers\Admin\StokMasukController@addDetail | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| DELETE | /admin/stok-masuk/detail/{detail_stok_masuk} | admin.stok-masuk.detail.destroy | App\Http\Controllers\Admin\StokMasukController@removeDetail | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/notifikasi | admin.notifikasi.index | App\Http\Controllers\Admin\NotifikasiController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/notifikasi/sync | admin.notifikasi.sync | App\Http\Controllers\Admin\NotifikasiController@sync | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/notifikasi/{notifikasi}/open | admin.notifikasi.open | App\Http\Controllers\Admin\NotifikasiController@open | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PATCH | /admin/notifikasi/{notifikasi}/read | admin.notifikasi.read | App\Http\Controllers\Admin\NotifikasiController@read | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/mobil | admin.mobil.index | App\Http\Controllers\Admin\MobilController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/mobil/create | admin.mobil.create | App\Http\Controllers\Admin\MobilController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/mobil | admin.mobil.store | App\Http\Controllers\Admin\MobilController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/mobil/{mobil} | admin.mobil.show | App\Http\Controllers\Admin\MobilController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/mobil/{mobil}/edit | admin.mobil.edit | App\Http\Controllers\Admin\MobilController@edit | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT,PATCH | /admin/mobil/{mobil} | admin.mobil.update | App\Http\Controllers\Admin\MobilController@update | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/mobil/{mobil}/stok | admin.mobil.stock | App\Http\Controllers\Admin\MobilController@stock | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/mobil/{mobil}/stok | admin.mobil.stock.adjust | App\Http\Controllers\Admin\MobilController@adjustStock | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/pre-order-mobil | admin.preorder-mobil.index | App\Http\Controllers\Admin\MemberVehiclePreorderController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/pesanan-mobil | admin.pesanan-mobil.index | App\Http\Controllers\Admin\MemberVehiclePurchaseController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/pesanan-mobil/{pesanan_mobil} | admin.pesanan-mobil.show | App\Http\Controllers\Admin\MemberVehiclePurchaseController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pesanan-mobil/{pesanan_mobil}/approve | admin.pesanan-mobil.approve | App\Http\Controllers\Admin\MemberVehiclePurchaseController@approve | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pesanan-mobil/{pesanan_mobil}/reject | admin.pesanan-mobil.reject | App\Http\Controllers\Admin\MemberVehiclePurchaseController@reject | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pesanan-mobil/{pesanan_mobil}/schedule | admin.pesanan-mobil.schedule | App\Http\Controllers\Admin\MemberVehiclePurchaseController@schedule | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pesanan-mobil/{pesanan_mobil}/pdi/start | admin.pesanan-mobil.pdi.start | App\Http\Controllers\Admin\MemberVehiclePurchaseController@startPdi | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pesanan-mobil/{pesanan_mobil}/pdi | admin.pesanan-mobil.pdi | App\Http\Controllers\Admin\MemberVehiclePurchaseController@pdi | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pesanan-mobil/{pesanan_mobil}/handover | admin.pesanan-mobil.handover | App\Http\Controllers\Admin\MemberVehiclePurchaseController@handover | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PATCH | /admin/mobil/{mobil}/nonaktifkan | admin.mobil.deactivate | App\Http\Controllers\Admin\MobilController@deactivate | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/mobil/{mobil}/foto | admin.mobil.foto.store | App\Http\Controllers\Admin\FotoMobilController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PATCH | /admin/mobil/{mobil}/foto/{foto}/primary | admin.mobil.foto.primary | App\Http\Controllers\Admin\FotoMobilController@primary | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| DELETE | /admin/mobil/{mobil}/foto/{foto} | admin.mobil.foto.destroy | App\Http\Controllers\Admin\FotoMobilController@destroy | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/booking-mobil | admin.booking.index | App\Http\Controllers\OperationsBookingController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/booking-mobil/{booking_mobil} | admin.booking.show | App\Http\Controllers\OperationsBookingController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/booking-mobil/{booking_mobil}/{action} | admin.booking.action | App\Http\Controllers\OperationsBookingController@action | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/pengajuan-mobil | admin.pengajuan-mobil.index | App\Http\Controllers\OperationsPengajuanMobilController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/pengajuan-mobil/{pengajuan_mobil} | admin.pengajuan-mobil.show | App\Http\Controllers\OperationsPengajuanMobilController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pengajuan-mobil/{pengajuan_mobil}/process | admin.pengajuan-mobil.process | App\Http\Controllers\OperationsPengajuanMobilController@process | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pengajuan-mobil/{pengajuan_mobil}/start | admin.pengajuan-mobil.start | App\Http\Controllers\OperationsPengajuanMobilController@start | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pengajuan-mobil/{pengajuan_mobil}/inspection | admin.pengajuan-mobil.inspection | App\Http\Controllers\OperationsPengajuanMobilController@inspection | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pengajuan-mobil/{pengajuan_mobil}/offer | admin.pengajuan-mobil.offer | App\Http\Controllers\OperationsPengajuanMobilController@offer | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/trade-in | admin.trade-in.index | App\Http\Controllers\OperationsTradeInController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/trade-in/{trade_in} | admin.trade-in.show | App\Http\Controllers\OperationsTradeInController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/trade-in/{trade_in}/finalize | admin.trade-in.finalize | App\Http\Controllers\OperationsTradeInController@finalize | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/trade-in/{trade_in}/cancel | admin.trade-in.cancel | App\Http\Controllers\OperationsTradeInController@cancel | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/invoice/trade-in/{trade_in} | admin.invoice.trade-in | App\Http\Controllers\TradeInInvoiceController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/transaksi-trade-in/{transaksi}/pembayaran/create | admin.trade-in.payment.create | App\Http\Controllers\PaymentController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/transaksi-trade-in/{transaksi}/pembayaran | admin.trade-in.payment.store | App\Http\Controllers\PaymentController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pengajuan-mobil/{pengajuan_mobil}/purchase | admin.pengajuan-mobil.purchase | App\Http\Controllers\UsedCarSettlementController@purchase | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pembelian-mobil/{pembelian_mobil}/finalize | admin.pembelian-mobil.finalize | App\Http\Controllers\UsedCarSettlementController@finalize | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/transaksi-pembelian-mobil/{transaksi}/pembayaran/create | admin.used-car.payment.create | App\Http\Controllers\OutgoingPaymentController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/transaksi-pembelian-mobil/{transaksi}/pembayaran | admin.used-car.payment.store | App\Http\Controllers\OutgoingPaymentController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pembayaran-keluar/{pembayaran}/verify | admin.used-car.payment.verify | App\Http\Controllers\OutgoingPaymentController@verify | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pembayaran-keluar/{pembayaran}/reject | admin.used-car.payment.reject | App\Http\Controllers\OutgoingPaymentController@reject | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/invoice/pembelian-mobil/{pembelian_mobil} | admin.invoice.used-car | App\Http\Controllers\UsedCarReceiptController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/test-drive | admin.test-drive.index | App\Http\Controllers\OperationsTestDriveController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/test-drive/{test_drive} | admin.test-drive.show | App\Http\Controllers\OperationsTestDriveController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/test-drive/{test_drive}/{action} | admin.test-drive.action | App\Http\Controllers\OperationsTestDriveController@action | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/booking-servis | admin.booking-servis.index | App\Http\Controllers\OperationsBookingServisController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/booking-servis/{booking_servis} | admin.booking-servis.show | App\Http\Controllers\OperationsBookingServisController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/booking-servis/{booking_servis}/{action} | admin.booking-servis.action | App\Http\Controllers\OperationsBookingServisController@action | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/servis | admin.servis.index | App\Http\Controllers\OperationsServisController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/servis/jadwal | admin.servis.schedule | App\Http\Controllers\Admin\ServiceScheduleController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/servis/personel | admin.servis.personnel | App\Http\Controllers\Admin\ServicePersonnelController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT | /admin/servis/personel/{profile}/profile | admin.servis.personnel.profile.update | App\Http\Controllers\Admin\ServicePersonnelController@updateProfile | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT | /admin/servis/personel/{profile}/jam-kerja | admin.servis.personnel.hours.update | App\Http\Controllers\Admin\ServicePersonnelController@updateHours | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/servis/personel/time-off | admin.servis.personnel.timeoff.store | App\Http\Controllers\Admin\ServicePersonnelController@storeTimeOff | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| DELETE | /admin/servis/personel/time-off/{timeOff} | admin.servis.personnel.timeoff.destroy | App\Http\Controllers\Admin\ServicePersonnelController@destroyTimeOff | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT | /admin/servis/jadwal/jam-operasional | admin.servis.schedule.hours | App\Http\Controllers\Admin\ServiceScheduleController@updateHours | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/servis/jadwal/blackout | admin.servis.schedule.blackouts.store | App\Http\Controllers\Admin\ServiceScheduleController@storeBlackout | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| DELETE | /admin/servis/jadwal/blackout/{blackout} | admin.servis.schedule.blackouts.destroy | App\Http\Controllers\Admin\ServiceScheduleController@destroyBlackout | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/servis/{servis} | admin.servis.show | App\Http\Controllers\OperationsServisController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access; assigned.wo |
| GET,HEAD | /admin/servis/{servis}/pemeriksaan | admin.servis.pemeriksaan | App\Http\Controllers\OperationsServisController@pemeriksaan | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access; assigned.wo |
| GET,HEAD | /admin/servis/{servis}/persetujuan | admin.servis.persetujuan | App\Http\Controllers\OperationsServisController@persetujuan | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access; assigned.wo |
| GET,HEAD | /admin/servis/{servis}/pengerjaan | admin.servis.pengerjaan | App\Http\Controllers\OperationsServisController@pengerjaan | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access; assigned.wo |
| GET,HEAD | /admin/servis/{servis}/quality-control | admin.servis.quality-control | App\Http\Controllers\OperationsServisController@qualityControl | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access; assigned.wo |
| GET,HEAD | /admin/servis/{servis}/finalisasi | admin.servis.finalisasi | App\Http\Controllers\OperationsServisController@finalisasi | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access; assigned.wo |
| GET,HEAD | /admin/servis/{servis}/spareparts/search | admin.servis.sparepart-search | App\Http\Controllers\Admin\SparepartController@search | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/servis/from-booking/{booking_servis} | admin.servis.from-booking | App\Http\Controllers\OperationsServisController@fromBooking | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/servis/{servis}/inspection | admin.servis.inspection | App\Http\Controllers\OperationsServisController@inspection | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/servis/{servis}/detail | admin.servis.detail.store | App\Http\Controllers\OperationsServisController@detail | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| DELETE | /admin/servis/{servis}/detail/{detail_servis} | admin.servis.detail.destroy | App\Http\Controllers\OperationsServisController@removeDetail | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/servis/{servis}/sparepart | admin.servis.sparepart.store | App\Http\Controllers\OperationsServisController@sparepart | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| DELETE | /admin/servis/{servis}/sparepart/{servis_sparepart} | admin.servis.sparepart.destroy | App\Http\Controllers\OperationsServisController@removeSparepart | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/servis/{servis}/finalize | admin.servis.finalize | App\Http\Controllers\OperationsServisController@finalize | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/servis/{servis}/findings | admin.servis.findings.store | App\Http\Controllers\ServiceExecutionController@storeFinding | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/servis/{servis}/approvals | admin.servis.approvals.store | App\Http\Controllers\ServiceExecutionController@storeApproval | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/servis/{servis}/approvals/{approval}/clarification | admin.servis.approvals.clarification | App\Http\Controllers\ServiceExecutionController@requestApprovalClarification | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/servis/{servis}/qc/start | admin.servis.qc.start | App\Http\Controllers\ServiceExecutionController@startQc | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT | /admin/servis/{servis}/qc | admin.servis.qc.update | App\Http\Controllers\ServiceExecutionController@updateQc | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT | /admin/servis/{servis}/assignment | admin.servis.assignment.update | App\Http\Controllers\ServiceAssignmentController@update | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/transaksi-servis/{transaksi}/pembayaran/create | admin.payment.create | App\Http\Controllers\PaymentController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/transaksi-servis/{transaksi}/pembayaran | admin.payment.store | App\Http\Controllers\PaymentController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/transaksi-mobil/{transaksi}/pembayaran/create | admin.car-sale.payment.create | App\Http\Controllers\CarSalePaymentController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/transaksi-mobil/{transaksi}/pembayaran | admin.car-sale.payment.store | App\Http\Controllers\CarSalePaymentController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/pembayaran/{pembayaran}/verify | admin.payment.verify | App\Http\Controllers\PaymentController@verify | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/invoice/servis/{transaksi} | admin.invoice.servis | App\Http\Controllers\InvoiceController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/invoice/car-wash/{transaksi} | admin.invoice.carwash | App\Http\Controllers\InvoiceController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/invoice/sparepart/{transaksi} | admin.invoice.sparepart | App\Http\Controllers\InvoiceController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/invoice/penjualan-mobil/{transaksi} | admin.invoice.car-sale | App\Http\Controllers\CarSaleInvoiceController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/booking-cuci | admin.booking-cuci.index | App\Http\Controllers\OperationsBookingCuciController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/booking-cuci/{booking_cuci} | admin.booking-cuci.show | App\Http\Controllers\OperationsBookingCuciController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/booking-cuci/{booking_cuci}/{action} | admin.booking-cuci.action | App\Http\Controllers\OperationsBookingCuciController@action | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/penjualan-sparepart | admin.penjualan-sparepart.index | App\Http\Controllers\OperationsPenjualanSparepartController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/penjualan-sparepart/create | admin.penjualan-sparepart.create | App\Http\Controllers\OperationsPenjualanSparepartController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/penjualan-sparepart | admin.penjualan-sparepart.store | App\Http\Controllers\OperationsPenjualanSparepartController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/penjualan-sparepart/{penjualan_sparepart} | admin.penjualan-sparepart.show | App\Http\Controllers\OperationsPenjualanSparepartController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/penjualan-sparepart/{penjualan_sparepart}/detail | admin.penjualan-sparepart.detail.store | App\Http\Controllers\OperationsPenjualanSparepartController@addDetail | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| PUT | /admin/penjualan-sparepart/detail/{detail_penjualan_sparepart} | admin.penjualan-sparepart.detail.update | App\Http\Controllers\OperationsPenjualanSparepartController@updateDetail | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| DELETE | /admin/penjualan-sparepart/detail/{detail_penjualan_sparepart} | admin.penjualan-sparepart.detail.destroy | App\Http\Controllers\OperationsPenjualanSparepartController@deleteDetail | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/penjualan-sparepart/{penjualan_sparepart}/finalize | admin.penjualan-sparepart.finalize | App\Http\Controllers\OperationsPenjualanSparepartController@finalize | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/penjualan-sparepart/{penjualan_sparepart}/cancel | admin.penjualan-sparepart.cancel | App\Http\Controllers\OperationsPenjualanSparepartController@cancel | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/penjualan-mobil | admin.penjualan-mobil.index | App\Http\Controllers\OperationsPenjualanMobilController@index | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/penjualan-mobil/create | admin.penjualan-mobil.create | App\Http\Controllers\OperationsPenjualanMobilController@create | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| POST | /admin/penjualan-mobil | admin.penjualan-mobil.store | App\Http\Controllers\OperationsPenjualanMobilController@store | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /admin/penjualan-mobil/{penjualan_mobil} | admin.penjualan-mobil.show | App\Http\Controllers\OperationsPenjualanMobilController@show | web; auth; role:admin,super_admin,owner,sales,service_advisor,mechanic,warehouse_staff,finance,operational_staff,quality_control; module.access |
| GET,HEAD | /petugas/sparepart | petugas.sparepart.index | App\Http\Controllers\Petugas\SparepartController@index | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/sparepart/{sparepart} | petugas.sparepart.show | App\Http\Controllers\Petugas\SparepartController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/mobil | petugas.mobil.index | App\Http\Controllers\Petugas\MobilController@index | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/mobil/{mobil} | petugas.mobil.show | App\Http\Controllers\Petugas\MobilController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/booking-mobil | petugas.booking.index | App\Http\Controllers\OperationsBookingController@index | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/booking-mobil/{booking_mobil} | petugas.booking.show | App\Http\Controllers\OperationsBookingController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/booking-mobil/{booking_mobil}/{action} | petugas.booking.action | App\Http\Controllers\OperationsBookingController@action | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/pengajuan-mobil | petugas.pengajuan-mobil.index | App\Http\Controllers\OperationsPengajuanMobilController@index | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/pengajuan-mobil/{pengajuan_mobil} | petugas.pengajuan-mobil.show | App\Http\Controllers\OperationsPengajuanMobilController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/pengajuan-mobil/{pengajuan_mobil}/process | petugas.pengajuan-mobil.process | App\Http\Controllers\OperationsPengajuanMobilController@process | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/pengajuan-mobil/{pengajuan_mobil}/start | petugas.pengajuan-mobil.start | App\Http\Controllers\OperationsPengajuanMobilController@start | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/pengajuan-mobil/{pengajuan_mobil}/inspection | petugas.pengajuan-mobil.inspection | App\Http\Controllers\OperationsPengajuanMobilController@inspection | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/pengajuan-mobil/{pengajuan_mobil}/offer | petugas.pengajuan-mobil.offer | App\Http\Controllers\OperationsPengajuanMobilController@offer | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/trade-in | petugas.trade-in.index | App\Http\Controllers\OperationsTradeInController@index | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/trade-in/{trade_in} | petugas.trade-in.show | App\Http\Controllers\OperationsTradeInController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/trade-in/{trade_in}/finalize | petugas.trade-in.finalize | App\Http\Controllers\OperationsTradeInController@finalize | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/trade-in/{trade_in}/cancel | petugas.trade-in.cancel | App\Http\Controllers\OperationsTradeInController@cancel | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/invoice/trade-in/{trade_in} | petugas.invoice.trade-in | App\Http\Controllers\TradeInInvoiceController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/transaksi-trade-in/{transaksi}/pembayaran/create | petugas.trade-in.payment.create | App\Http\Controllers\PaymentController@create | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/transaksi-trade-in/{transaksi}/pembayaran | petugas.trade-in.payment.store | App\Http\Controllers\PaymentController@store | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/pengajuan-mobil/{pengajuan_mobil}/purchase | petugas.pengajuan-mobil.purchase | App\Http\Controllers\UsedCarSettlementController@purchase | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/pembelian-mobil/{pembelian_mobil}/finalize | petugas.pembelian-mobil.finalize | App\Http\Controllers\UsedCarSettlementController@finalize | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/transaksi-pembelian-mobil/{transaksi}/pembayaran/create | petugas.used-car.payment.create | App\Http\Controllers\OutgoingPaymentController@create | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/transaksi-pembelian-mobil/{transaksi}/pembayaran | petugas.used-car.payment.store | App\Http\Controllers\OutgoingPaymentController@store | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/pembayaran-keluar/{pembayaran}/verify | petugas.used-car.payment.verify | App\Http\Controllers\OutgoingPaymentController@verify | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/pembayaran-keluar/{pembayaran}/reject | petugas.used-car.payment.reject | App\Http\Controllers\OutgoingPaymentController@reject | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/invoice/pembelian-mobil/{pembelian_mobil} | petugas.invoice.used-car | App\Http\Controllers\UsedCarReceiptController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/test-drive | petugas.test-drive.index | App\Http\Controllers\OperationsTestDriveController@index | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/test-drive/{test_drive} | petugas.test-drive.show | App\Http\Controllers\OperationsTestDriveController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/test-drive/{test_drive}/{action} | petugas.test-drive.action | App\Http\Controllers\OperationsTestDriveController@action | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/booking-servis | petugas.booking-servis.index | App\Http\Controllers\OperationsBookingServisController@index | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/booking-servis/{booking_servis} | petugas.booking-servis.show | App\Http\Controllers\OperationsBookingServisController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/booking-servis/{booking_servis}/{action} | petugas.booking-servis.action | App\Http\Controllers\OperationsBookingServisController@action | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/servis | petugas.servis.index | App\Http\Controllers\OperationsServisController@index | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/servis/{servis} | petugas.servis.show | App\Http\Controllers\OperationsServisController@show | web; auth; role:admin,super_admin,petugas,service_advisor; assigned.wo |
| GET,HEAD | /petugas/servis/{servis}/pemeriksaan | petugas.servis.pemeriksaan | App\Http\Controllers\OperationsServisController@pemeriksaan | web; auth; role:admin,super_admin,petugas,service_advisor; assigned.wo |
| GET,HEAD | /petugas/servis/{servis}/persetujuan | petugas.servis.persetujuan | App\Http\Controllers\OperationsServisController@persetujuan | web; auth; role:admin,super_admin,petugas,service_advisor; assigned.wo |
| GET,HEAD | /petugas/servis/{servis}/pengerjaan | petugas.servis.pengerjaan | App\Http\Controllers\OperationsServisController@pengerjaan | web; auth; role:admin,super_admin,petugas,service_advisor; assigned.wo |
| GET,HEAD | /petugas/servis/{servis}/quality-control | petugas.servis.quality-control | App\Http\Controllers\OperationsServisController@qualityControl | web; auth; role:admin,super_admin,petugas,service_advisor; assigned.wo |
| GET,HEAD | /petugas/servis/{servis}/finalisasi | petugas.servis.finalisasi | App\Http\Controllers\OperationsServisController@finalisasi | web; auth; role:admin,super_admin,petugas,service_advisor; assigned.wo |
| GET,HEAD | /petugas/servis/{servis}/spareparts/search | petugas.servis.sparepart-search | App\Http\Controllers\Admin\SparepartController@search | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/servis/from-booking/{booking_servis} | petugas.servis.from-booking | App\Http\Controllers\OperationsServisController@fromBooking | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/servis/{servis}/inspection | petugas.servis.inspection | App\Http\Controllers\OperationsServisController@inspection | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/servis/{servis}/detail | petugas.servis.detail.store | App\Http\Controllers\OperationsServisController@detail | web; auth; role:admin,super_admin,petugas,service_advisor |
| DELETE | /petugas/servis/{servis}/detail/{detail_servis} | petugas.servis.detail.destroy | App\Http\Controllers\OperationsServisController@removeDetail | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/servis/{servis}/sparepart | petugas.servis.sparepart.store | App\Http\Controllers\OperationsServisController@sparepart | web; auth; role:admin,super_admin,petugas,service_advisor |
| DELETE | /petugas/servis/{servis}/sparepart/{servis_sparepart} | petugas.servis.sparepart.destroy | App\Http\Controllers\OperationsServisController@removeSparepart | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/servis/{servis}/finalize | petugas.servis.finalize | App\Http\Controllers\OperationsServisController@finalize | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/servis/{servis}/findings | petugas.servis.findings.store | App\Http\Controllers\ServiceExecutionController@storeFinding | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/servis/{servis}/approvals | petugas.servis.approvals.store | App\Http\Controllers\ServiceExecutionController@storeApproval | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/servis/{servis}/approvals/{approval}/clarification | petugas.servis.approvals.clarification | App\Http\Controllers\ServiceExecutionController@requestApprovalClarification | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/servis/{servis}/qc/start | petugas.servis.qc.start | App\Http\Controllers\ServiceExecutionController@startQc | web; auth; role:admin,super_admin,petugas,service_advisor |
| PUT | /petugas/servis/{servis}/qc | petugas.servis.qc.update | App\Http\Controllers\ServiceExecutionController@updateQc | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/transaksi-servis/{transaksi}/pembayaran/create | petugas.payment.create | App\Http\Controllers\PaymentController@create | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/transaksi-servis/{transaksi}/pembayaran | petugas.payment.store | App\Http\Controllers\PaymentController@store | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/transaksi-mobil/{transaksi}/pembayaran/create | petugas.car-sale.payment.create | App\Http\Controllers\CarSalePaymentController@create | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/transaksi-mobil/{transaksi}/pembayaran | petugas.car-sale.payment.store | App\Http\Controllers\CarSalePaymentController@store | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/pembayaran/{pembayaran}/verify | petugas.payment.verify | App\Http\Controllers\PaymentController@verify | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/pembayaran/{pembayaran}/reject | petugas.payment.reject | App\Http\Controllers\PaymentController@reject | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/invoice/servis/{transaksi} | petugas.invoice.servis | App\Http\Controllers\InvoiceController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/invoice/car-wash/{transaksi} | petugas.invoice.carwash | App\Http\Controllers\InvoiceController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/invoice/sparepart/{transaksi} | petugas.invoice.sparepart | App\Http\Controllers\InvoiceController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/invoice/penjualan-mobil/{transaksi} | petugas.invoice.car-sale | App\Http\Controllers\CarSaleInvoiceController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/booking-cuci | petugas.booking-cuci.index | App\Http\Controllers\OperationsBookingCuciController@index | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/booking-cuci/{booking_cuci} | petugas.booking-cuci.show | App\Http\Controllers\OperationsBookingCuciController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/booking-cuci/{booking_cuci}/{action} | petugas.booking-cuci.action | App\Http\Controllers\OperationsBookingCuciController@action | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/penjualan-sparepart | petugas.penjualan-sparepart.index | App\Http\Controllers\OperationsPenjualanSparepartController@index | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/penjualan-sparepart/create | petugas.penjualan-sparepart.create | App\Http\Controllers\OperationsPenjualanSparepartController@create | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/penjualan-sparepart | petugas.penjualan-sparepart.store | App\Http\Controllers\OperationsPenjualanSparepartController@store | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/penjualan-sparepart/{penjualan_sparepart} | petugas.penjualan-sparepart.show | App\Http\Controllers\OperationsPenjualanSparepartController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/penjualan-sparepart/{penjualan_sparepart}/detail | petugas.penjualan-sparepart.detail.store | App\Http\Controllers\OperationsPenjualanSparepartController@addDetail | web; auth; role:admin,super_admin,petugas,service_advisor |
| PUT | /petugas/penjualan-sparepart/detail/{detail_penjualan_sparepart} | petugas.penjualan-sparepart.detail.update | App\Http\Controllers\OperationsPenjualanSparepartController@updateDetail | web; auth; role:admin,super_admin,petugas,service_advisor |
| DELETE | /petugas/penjualan-sparepart/detail/{detail_penjualan_sparepart} | petugas.penjualan-sparepart.detail.destroy | App\Http\Controllers\OperationsPenjualanSparepartController@deleteDetail | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/penjualan-sparepart/{penjualan_sparepart}/finalize | petugas.penjualan-sparepart.finalize | App\Http\Controllers\OperationsPenjualanSparepartController@finalize | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/penjualan-mobil | petugas.penjualan-mobil.index | App\Http\Controllers\OperationsPenjualanMobilController@index | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/penjualan-mobil/create | petugas.penjualan-mobil.create | App\Http\Controllers\OperationsPenjualanMobilController@create | web; auth; role:admin,super_admin,petugas,service_advisor |
| POST | /petugas/penjualan-mobil | petugas.penjualan-mobil.store | App\Http\Controllers\OperationsPenjualanMobilController@store | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /petugas/penjualan-mobil/{penjualan_mobil} | petugas.penjualan-mobil.show | App\Http\Controllers\OperationsPenjualanMobilController@show | web; auth; role:admin,super_admin,petugas,service_advisor |
| GET,HEAD | /storage/{path} | storage.local | Closure |  |
| PUT | /storage/{path} | storage.local.upload | Closure |  |

## app/Models

- app/Models/BookingCuci.php
- app/Models/BookingMobil.php
- app/Models/BookingServis.php
- app/Models/DetailPenjualanSparepart.php
- app/Models/DetailServis.php
- app/Models/DetailStokMasuk.php
- app/Models/EventPembayaranGateway.php
- app/Models/FinancialAdjustment.php
- app/Models/FinancialAdjustmentEvent.php
- app/Models/FotoMobil.php
- app/Models/InspeksiMobil.php
- app/Models/JenisServis.php
- app/Models/KategoriSparepart.php
- app/Models/KendaraanPelanggan.php
- app/Models/Membership.php
- app/Models/MemberVehiclePreorder.php
- app/Models/Mobil.php
- app/Models/MobilGaleri.php
- app/Models/MobilWarna.php
- app/Models/Notifikasi.php
- app/Models/PaketCuci.php
- app/Models/PdiPesananMobil.php
- app/Models/Pelanggan.php
- app/Models/Pembayaran.php
- app/Models/PembayaranGateway.php
- app/Models/PembelianMobilBekas.php
- app/Models/PengajuanMobil.php
- app/Models/Pengaturan.php
- app/Models/PenjualanMobil.php
- app/Models/PenjualanSparepart.php
- app/Models/Permission.php
- app/Models/PesananMobilMember.php
- app/Models/Petugas.php
- app/Models/PoinMember.php
- app/Models/PromoMember.php
- app/Models/PromoRedemption.php
- app/Models/PromoSimulation.php
- app/Models/RiwayatPoin.php
- app/Models/RiwayatStok.php
- app/Models/Role.php
- app/Models/SalesAssignmentAudit.php
- app/Models/SalesAward.php
- app/Models/SalesAwardAudit.php
- app/Models/SalesAwardResult.php
- app/Models/SalesLead.php
- app/Models/SalesLeadActivity.php
- app/Models/SalesTarget.php
- app/Models/SerahTerimaPesananMobil.php
- app/Models/ServiceApprovalRequest.php
- app/Models/ServiceApprovalRequestItem.php
- app/Models/ServiceBlackoutDate.php
- app/Models/ServiceExecutionEvent.php
- app/Models/ServiceFinding.php
- app/Models/ServiceOperatingHour.php
- app/Models/ServiceQualityControl.php
- app/Models/ServiceQualityControlItem.php
- app/Models/ServiceSpecialization.php
- app/Models/ServiceStaffProfile.php
- app/Models/ServiceWorkOrderAssignment.php
- app/Models/Servis.php
- app/Models/ServisSparepart.php
- app/Models/Sparepart.php
- app/Models/SparepartFitment.php
- app/Models/StokMasuk.php
- app/Models/Supplier.php
- app/Models/TechnicianTimeOff.php
- app/Models/TechnicianWorkingHour.php
- app/Models/TestDrive.php
- app/Models/TradeIn.php
- app/Models/Transaksi.php
- app/Models/User.php
- app/Models/VehicleInventory.php
- app/Models/VehicleInventoryHistory.php

## app/Services

- app/Services/AdminNotificationBadgeService.php
- app/Services/BookingCuciService.php
- app/Services/BookingMobilService.php
- app/Services/BookingServisService.php
- app/Services/FinancialAdjustmentService.php
- app/Services/GatewayPaymentGuard.php
- app/Services/IndonesiaRegionService.php
- app/Services/InventoryService.php
- app/Services/MasterAuditService.php
- app/Services/MemberAccessService.php
- app/Services/MemberDashboardService.php
- app/Services/MemberIdentityService.php
- app/Services/MembershipPaymentService.php
- app/Services/MemberVehiclePreorderService.php
- app/Services/MemberVehiclePurchaseService.php
- app/Services/MidtransClient.php
- app/Services/MidtransPaymentService.php
- app/Services/MobilAuditService.php
- app/Services/OutgoingPaymentService.php
- app/Services/OwnerAnalyticsService.php
- app/Services/OwnerOverviewService.php
- app/Services/PasswordCompatibilityService.php
- app/Services/PaymentService.php
- app/Services/PembelianMobilBekasService.php
- app/Services/PengajuanMobilService.php
- app/Services/PengaturanService.php
- app/Services/PenjualanMobilService.php
- app/Services/PenjualanSparepartService.php
- app/Services/PromoAnalyticsService.php
- app/Services/PromoEngineService.php
- app/Services/PromoMemberService.php
- app/Services/PromoSimulationService.php
- app/Services/SalesAwardsService.php
- app/Services/SalesManagementService.php
- app/Services/ServiceAssignmentService.php
- app/Services/ServiceExecutionService.php
- app/Services/ServiceNotificationService.php
- app/Services/ServiceOperationsDashboardService.php
- app/Services/ServicePersonnelAvailabilityService.php
- app/Services/ServiceReminderService.php
- app/Services/ServiceSchedulingService.php
- app/Services/ServiceStatusPresenter.php
- app/Services/ServisService.php
- app/Services/ServisSparepartService.php
- app/Services/SparepartFitmentService.php
- app/Services/TestDriveService.php
- app/Services/TradeInService.php
- app/Services/VehicleAvailabilityService.php
- app/Services/VehicleGalleryService.php
- app/Services/VehicleInventoryService.php
- app/Services/VerifiedCarOwnershipService.php
- app/Services/WorkOrderEstimateService.php
- app/Services/WorkOrderSourceService.php
- app/Services/WorkOrderStageService.php
- app/Services/YzaPointService.php

## app/Http/Controllers

- app/Http/Controllers/Admin/AdminAuxiliaryController.php
- app/Http/Controllers/Admin/AdminWorkspaceController.php
- app/Http/Controllers/Admin/FotoMobilController.php
- app/Http/Controllers/Admin/InventoryController.php
- app/Http/Controllers/Admin/JenisServisController.php
- app/Http/Controllers/Admin/KategoriSparepartController.php
- app/Http/Controllers/Admin/LaporanServisController.php
- app/Http/Controllers/Admin/MembershipController.php
- app/Http/Controllers/Admin/MemberVehiclePreorderController.php
- app/Http/Controllers/Admin/MemberVehiclePurchaseController.php
- app/Http/Controllers/Admin/MobilController.php
- app/Http/Controllers/Admin/NotifikasiController.php
- app/Http/Controllers/Admin/PaketCuciController.php
- app/Http/Controllers/Admin/PaymentManagementController.php
- app/Http/Controllers/Admin/PelangganController.php
- app/Http/Controllers/Admin/PengaturanController.php
- app/Http/Controllers/Admin/PetugasController.php
- app/Http/Controllers/Admin/PromoAnalyticsController.php
- app/Http/Controllers/Admin/PromoMemberController.php
- app/Http/Controllers/Admin/PromoSimulationController.php
- app/Http/Controllers/Admin/ServicePersonnelController.php
- app/Http/Controllers/Admin/ServiceScheduleController.php
- app/Http/Controllers/Admin/SparepartController.php
- app/Http/Controllers/Admin/StokMasukController.php
- app/Http/Controllers/Admin/SupplierController.php
- app/Http/Controllers/AuthController.php
- app/Http/Controllers/CarSaleInvoiceController.php
- app/Http/Controllers/CarSalePaymentController.php
- app/Http/Controllers/Controller.php
- app/Http/Controllers/DashboardController.php
- app/Http/Controllers/FinancialAdjustmentController.php
- app/Http/Controllers/InvoiceController.php
- app/Http/Controllers/MembershipController.php
- app/Http/Controllers/MidtransController.php
- app/Http/Controllers/NotifikasiController.php
- app/Http/Controllers/OperationsBookingController.php
- app/Http/Controllers/OperationsBookingCuciController.php
- app/Http/Controllers/OperationsBookingServisController.php
- app/Http/Controllers/OperationsPengajuanMobilController.php
- app/Http/Controllers/OperationsPenjualanMobilController.php
- app/Http/Controllers/OperationsPenjualanSparepartController.php
- app/Http/Controllers/OperationsServisController.php
- app/Http/Controllers/OperationsTestDriveController.php
- app/Http/Controllers/OperationsTradeInController.php
- app/Http/Controllers/OutgoingPaymentController.php
- app/Http/Controllers/OwnerDashboardController.php
- app/Http/Controllers/PaymentController.php
- app/Http/Controllers/Pelanggan/BookingCuciController.php
- app/Http/Controllers/Pelanggan/BookingMobilController.php
- app/Http/Controllers/Pelanggan/BookingServisController.php
- app/Http/Controllers/Pelanggan/KendaraanController.php
- app/Http/Controllers/Pelanggan/MemberVehiclePreorderController.php
- app/Http/Controllers/Pelanggan/MemberVehiclePurchaseController.php
- app/Http/Controllers/Pelanggan/PengajuanMobilController.php
- app/Http/Controllers/Pelanggan/PointController.php
- app/Http/Controllers/Pelanggan/PromoMemberController.php
- app/Http/Controllers/Pelanggan/PromoPreviewController.php
- app/Http/Controllers/Pelanggan/ServiceReminderController.php
- app/Http/Controllers/Pelanggan/ServisController.php
- app/Http/Controllers/Pelanggan/TestDriveController.php
- app/Http/Controllers/Pelanggan/TradeInController.php
- app/Http/Controllers/Petugas/MobilController.php
- app/Http/Controllers/Petugas/ProfileController.php
- app/Http/Controllers/Petugas/SparepartController.php
- app/Http/Controllers/ProfileController.php
- app/Http/Controllers/Public/MobilController.php
- app/Http/Controllers/RegistrationController.php
- app/Http/Controllers/RoleDashboardController.php
- app/Http/Controllers/SalesManagementController.php
- app/Http/Controllers/ServiceAssignmentController.php
- app/Http/Controllers/ServiceExecutionController.php
- app/Http/Controllers/TradeInInvoiceController.php
- app/Http/Controllers/UsedCarReceiptController.php
- app/Http/Controllers/UsedCarSettlementController.php

## app/Http/Middleware

- app/Http/Middleware/AssignedWorkOrderMiddleware.php
- app/Http/Middleware/EnsureActiveMembership.php
- app/Http/Middleware/EnsureVerifiedCarOwnership.php
- app/Http/Middleware/PermissionMiddleware.php
- app/Http/Middleware/RoleMiddleware.php
- app/Http/Middleware/RoleModuleAccessMiddleware.php

## database/migrations

- database/migrations/2026_09_28_000000_create_yza_identity_tables.php
- database/migrations/2026_09_28_010000_create_mobil_tables.php
- database/migrations/2026_09_28_020000_create_sparepart_master_tables.php
- database/migrations/2026_09_28_030000_create_kendaraan_pelanggan_table.php
- database/migrations/2026_09_28_040000_create_service_carwash_settings_tables.php
- database/migrations/2026_09_29_050000_create_inventory_tables.php
- database/migrations/2026_09_29_060000_create_booking_test_drive_tables.php
- database/migrations/2026_09_29_070000_create_booking_servis_table.php
- database/migrations/2026_09_29_080000_create_booking_cuci_table.php
- database/migrations/2026_09_29_090000_create_servis_workorder_tables.php
- database/migrations/2026_09_29_100000_create_servis_sparepart_table.php
- database/migrations/2026_09_29_110000_create_transaksi_table.php
- database/migrations/2026_09_29_120000_create_pembayaran_table.php
- database/migrations/2026_09_29_130000_create_penjualan_sparepart_tables.php
- database/migrations/2026_09_29_140000_create_penjualan_mobil_table.php
- database/migrations/2026_09_29_150000_create_used_car_submission_tables.php
- database/migrations/2026_09_29_160000_create_pembelian_mobil_bekas_table.php
- database/migrations/2026_09_30_170000_create_trade_in_table.php
- database/migrations/2026_09_30_180000_create_yza_points_tables.php
- database/migrations/2026_09_30_190000_create_promo_member_table.php
- database/migrations/2026_09_30_200000_create_midtrans_gateway_tables.php
- database/migrations/2026_10_03_210000_add_toyota_gr_supra_to_catalog.php
- database/migrations/2026_10_03_220000_add_toyota_supra_catalog_variant.php
- database/migrations/2026_10_04_010000_rename_toyota_supra_to_mk4.php
- database/migrations/2026_10_04_020000_create_memberships_table.php
- database/migrations/2026_10_04_021000_extend_gateway_for_membership.php
- database/migrations/2026_10_04_022000_add_membership_fee_setting.php
- database/migrations/2026_10_04_023000_upgrade_promo_engine.php
- database/migrations/2026_10_04_024000_add_promo_snapshot_to_transactions.php
- database/migrations/2026_10_04_025000_add_promo_projection_fields.php
- database/migrations/2026_10_04_026000_add_redemption_status.php
- database/migrations/2026_10_04_027000_add_trade_in_promo_scope.php
- database/migrations/2026_10_04_028000_add_spk_association_to_promo.php
- database/migrations/2026_10_04_029000_create_promo_simulations.php
- database/migrations/2026_10_04_030000_create_mobil_gallery_variants.php
- database/migrations/2026_10_04_031000_replace_duplicate_used_avanza_catalog.php
- database/migrations/2026_10_04_040000_add_structured_address_to_pelanggan_table.php
- database/migrations/2026_10_10_010000_create_member_vehicle_purchase_tables.php
- database/migrations/2026_10_10_020000_add_limited_edition_to_mobil.php
- database/migrations/2026_10_10_030000_add_showcase_slug_to_mobil.php
- database/migrations/2026_10_11_040000_create_member_vehicle_preorders_table.php
- database/migrations/2026_10_11_050000_create_vehicle_inventory_tables.php
- database/migrations/2026_10_11_120000_add_payment_verification_audit_fields.php
- database/migrations/2026_10_11_210000_add_finish_url_to_pembayaran_gateway.php
- database/migrations/2026_10_11_220000_add_pdi_pickup_ownership_workflow.php
- database/migrations/2026_10_12_010000_add_yza_service_scheduling_phase2a.php
- database/migrations/2026_10_12_020000_add_yza_service_personnel_phase3a.php
- database/migrations/2026_10_12_030000_add_yza_service_execution_phase4a.php
- database/migrations/2026_10_13_010000_add_sparepart_fitment_metadata.php
- database/migrations/2026_10_15_010000_create_yza_permissions_tables.php
- database/migrations/2026_10_15_020000_add_yza_service_assignment_history.php
- database/migrations/2026_10_15_030000_create_sales_management_tables.php
- database/migrations/2026_10_15_040000_create_sales_awards_tables.php
- database/migrations/2026_10_15_050000_create_financial_adjustments_tables.php

## tests

- tests/Feature/AdminDashboardRegressionTest.php
- tests/Feature/AdminPaymentManagementTest.php
- tests/Feature/AdminRealtimeNotificationsTest.php
- tests/Feature/AdminWorkspaceNavigationTest.php
- tests/Feature/AuthMemberLoginRegressionTest.php
- tests/Feature/AuthNavigationHardeningTest.php
- tests/Feature/BookingCuciStage4DTest.php
- tests/Feature/BookingServisStage4CTest.php
- tests/Feature/BookingTestDriveStage4BTest.php
- tests/Feature/CarSalesPaymentStage7BTest.php
- tests/Feature/CarSalesStage7ATest.php
- tests/Feature/CarWashFinalizationStage5ETest.php
- tests/Feature/ExampleTest.php
- tests/Feature/FinalQaReleaseReadinessTest.php
- tests/Feature/FinalSystemAuditTest.php
- tests/Feature/FinancialAdjustmentStage7Test.php
- tests/Feature/IdentityStage3CTest.php
- tests/Feature/InventoryStage4ATest.php
- tests/Feature/MemberFoundationStage10ATest.php
- tests/Feature/MemberServiceExecutionPhase4BTest.php
- tests/Feature/MemberVehicleCheckoutViewTest.php
- tests/Feature/MemberVehiclePurchaseFlowTest.php
- tests/Feature/MidtransSandboxStage11ATest.php
- tests/Feature/OwnerDashboardRedesignTest.php
- tests/Feature/OwnerExecutiveDashboardStage4Test.php
- tests/Feature/PromoServiceReminderStage10CTest.php
- tests/Feature/RegistrationNavigationTest.php
- tests/Feature/SalesExcellenceAwardsStage5Test.php
- tests/Feature/SalesManagementStage3Test.php
- tests/Feature/ServiceAssignmentStage2Test.php
- tests/Feature/ServiceCarwashSettingsTest.php
- tests/Feature/ServiceExecutionPhase4ATest.php
- tests/Feature/ServicePaymentStage5DTest.php
- tests/Feature/ServicePersonnelPhase3ATest.php
- tests/Feature/ServicePersonnelPhase3CIntegrationTest.php
- tests/Feature/ServiceSchedulingPhase2A1Test.php
- tests/Feature/ServiceWorkflowRefinementTest.php
- tests/Feature/ServisFinalizationStage5CTest.php
- tests/Feature/ServisSparepartStage5BTest.php
- tests/Feature/ServisStage5ABoundaryTest.php
- tests/Feature/ServisStage5ATest.php
- tests/Feature/SparepartFitmentDependencyTest.php
- tests/Feature/SparepartMasterTest.php
- tests/Feature/SparepartSalesPaymentStage6BTest.php
- tests/Feature/SparepartSalesStage6ATest.php
- tests/Feature/Stage14FinalQaTest.php
- tests/Feature/TradeInStage9ATest.php
- tests/Feature/UsedCarPurchaseSettlementStage8BTest.php
- tests/Feature/UsedCarSubmissionStage8ATest.php
- tests/Feature/VehicleAggregateInventoryFlowTest.php
- tests/Feature/VehicleAvailabilityStockTest.php
- tests/Feature/VehicleConfiguratorTest.php
- tests/Feature/VerifiedCarOwnershipTest.php
- tests/Feature/WorkOrderWizardPhaseTest.php
- tests/Feature/YzaMemberLandingTest.php
- tests/Feature/YzaPointsStage10BTest.php
- tests/Feature/YzaRoleManagementTest.php
- tests/TestCase.php
- tests/Unit/ExampleTest.php
