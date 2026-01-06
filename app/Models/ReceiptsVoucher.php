<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptsVoucher extends Model
{
    use HasFactory;

    // 🔥 EXACT TABLE NAME (MUST MATCH DB)
    protected $table = 'receipts_vouchers';

    protected $guarded = [];

    /* ===========================
       GENERATE RVID (CORRECT)
    =========================== */
    public static function generateRVID()
    {
        $prefix = 'RV-';

        $last = self::orderBy('id', 'desc')->first();

        $lastNumber = 0;
        if ($last && $last->rvid) {
            $lastNumber = (int) filter_var($last->rvid, FILTER_SANITIZE_NUMBER_INT);
        }

        return $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }

    /* ===========================
       RELATIONS
    =========================== */

    public function Vendor()
    {
        return $this->belongsTo(Vendor::class, 'party_id', 'id');
    }

    public function AccountHead()
    {
        return $this->belongsTo(AccountHead::class, 'row_account_head', 'id');
    }

    public function Account()
    {
        return $this->belongsTo(Account::class, 'row_account_id', 'id');
    }
}
