<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Certificate;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function myCertificates(Request $r){
        return Certificate::with('registration.event')
            ->whereHas('registration', fn($q) => $q->where('user_id', $r->user()->id))
            ->orderByDesc('issued_at')
            ->get();
    }

    // publik
    public function search(Request $r){
        $r->validate(['q' => 'required|string|max:100']);
        $q = $r->q;

        return Certificate::with(['registration.user','registration.event'])
            ->where('serial_number','like',"%$q%")
            ->orWhereHas('registration.user', fn($w) =>
                $w->where('name','like',"%$q%")
                  ->orWhere('email','like',"%$q%")
            )
            ->orWhereHas('registration.event', fn($w) =>
                $w->where('title','like',"%$q%")
            )
            ->limit(50)
            ->get();
    }

    public function download(Certificate $certificate){
        // bisa kasih link sementara (signed url) atau langsung response download
        return response()->download(storage_path('app/public/'.$certificate->file_path));
    }
}
