<?php

namespace App\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Paket;
use App\Models\Rekening;
use App\Models\Tagihan;
use Filament\Notifications\Notification;

class SidebarSubscriptionWidget extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function render()
    {
        return view('livewire.sidebar-subscription-widget');
    }

    // Logic: Hanya tampilkan jika user adalah Admin Sekolah
    public function shouldRender(): bool
    {
        $user = Auth::user();
        return $user && $user->sekolah_id !== null && $user->peran === 'admin_sekolah';
    }

    // --- ACTION: UPGRADE PAKET (Popup) ---
    public function upgradeAction(): Action
    {
        return Action::make('upgrade')
            ->label('Upgrade')
            ->color('primary')
            ->size('xs') // Ukuran tombol kecil
            ->button() // Tipe tombol
            ->modalHeading('Upgrade Paket Langganan')
            ->form([
                Select::make('paket_id')
                    ->label('Pilih Paket')
                    ->options(Paket::where('is_active', true)->where('harga', '>', 0)->pluck('nama_paket', 'id'))
                    ->required()
                    ->reactive(),
                Select::make('rekening_id')
                    ->label('Transfer ke Bank')
                    ->options(Rekening::where('is_active', true)->get()->mapWithKeys(function ($item) {
                        return [$item->id => "{$item->nama_bank} - {$item->nomor_rekening}"];
                    }))
                    ->required(),
            ])
            ->action(function (array $data) {
                $sekolah = Auth::user()->sekolah;
                $paket = Paket::find($data['paket_id']);

                Tagihan::create([
                    'sekolah_id' => $sekolah->id,
                    'paket_id' => $paket->id,
                    'rekening_id' => $data['rekening_id'],
                    'jumlah_bayar' => $paket->harga,
                    'status' => 'pending',
                ]);

                Notification::make()->success()->title('Invoice Dibuat')->send();
                
                // Redirect ke menu Tagihan
                return redirect()->to(\App\Filament\Resources\TagihanResource::getUrl('index'));
            });
    }
}