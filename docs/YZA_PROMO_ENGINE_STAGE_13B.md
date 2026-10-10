# YZA Promo Engine Stage 13B

`PromoMember` tetap menjadi katalog existing dan diperluas dengan quota, usage per member, dan member-only. `PromoEngineService` melakukan validasi server-side untuk periode, scope, minimum transaksi, membership, quota, dan batas penggunaan. Persentase mendukung maksimum potongan; fixed amount juga didukung.

`promo_redemptions` menyimpan snapshot subtotal, discount, total final, customer, transaction, dan waktu penggunaan. Redeem dikunci dalam transaction dan unik per promo/transaksi. Used Car outgoing payout tidak termasuk scope.
