<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdatePromoMemberRequest extends StorePromoMemberRequest
{
    public function rules(): array
    {
        $promo = $this->route('promo_member'); $id = $promo instanceof \App\Models\PromoMember ? $promo->id_promo : $promo;
        $rules = parent::rules(); $rules['kode_promo'] = ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('promo_member', 'kode_promo')->ignore($id, 'id_promo')];
        return $rules;
    }
}
