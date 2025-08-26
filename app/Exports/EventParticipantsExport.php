<?php

namespace App\Exports;

use App\Models\Registration;
use Illuminate\Http\Response;

class EventParticipantsExport
{
    protected $eventId;

    public function __construct($eventId = null)
    {
        $this->eventId = $eventId;
    }

    public function export($format = 'csv')
    {
        $data = $this->getData();
        
        if ($format === 'csv') {
            return $this->exportToCsv($data);
        }
        
        // Default to CSV
        return $this->exportToCsv($data);
    }

    protected function getData()
    {
        $query = Registration::with(['user', 'event', 'attendance', 'certificate']);
        
        if ($this->eventId) {
            $query->where('event_id', $this->eventId);
        }
        
        return $query->get();
    }

    protected function exportToCsv($data)
    {
        $filename = $this->eventId 
            ? "participants-event-{$this->eventId}.csv"
            : "all-participants.csv";

        $headers = [
            'ID',
            'Nama Peserta',
            'Email',
            'Nama Event',
            'Status Registrasi',
            'Tanggal Registrasi',
            'Status Kehadiran',
            'Status Sertifikat',
            'Token Sent At'
        ];

        $csvContent = $this->arrayToCsv($headers);
        
        foreach ($data as $registration) {
            $row = [
                $registration->id,
                $registration->user->name ?? 'N/A',
                $registration->user->email ?? 'N/A',
                $registration->event->title ?? 'N/A',
                $this->getStatusText($registration->status),
                $registration->created_at->format('d/m/Y H:i'),
                $registration->attendance ? 'Hadir' : 'Tidak Hadir',
                $registration->certificate ? 'Sudah Diterbitkan' : 'Belum Diterbitkan',
                $registration->token_sent_at ? $registration->token_sent_at->format('d/m/Y H:i') : 'N/A'
            ];
            
            $csvContent .= $this->arrayToCsv($row);
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    protected function arrayToCsv($array)
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $array);
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    private function getStatusText($status)
    {
        $statusMap = [
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'cancelled' => 'Dibatalkan',
            'rejected' => 'Ditolak'
        ];

        return $statusMap[$status] ?? $status;
    }
}
