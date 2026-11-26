<?php

namespace App\Http\Controllers\AmigoSecreto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AmigoSecreto\Participant;
use App\Models\AmigoSecreto\SecretReveal;
use App\Models\AmigoSecreto\Assignment;
use App\Models\AmigoSecreto\Group;
use App\Mail\SecretFriendNotification;
use App\Services\WhatsAppService;
use App\Jobs\SendWhatsAppMessageJob;
use Illuminate\Support\Facades\Hash;
use App\Helpers\AccessHelper;


class RevealController extends Controller
{
    // GET /reveal/{participant}
    public function showForm(Participant $participant, Request $request)
    {
        // ATUALIZA ÚLTIMO ACESSO
        $participant->update([
            'last_access_at' => now()
        ]);

        // Busca o reveal
        $reveal = SecretReveal::where('participant_id', $participant->id)->first();

        $autofillKey = $request->query('autofill_key', null);

        // se já existe senha -> mostra tela de digitar senha
        if ($reveal) {
            return view('amigo-secreto.reveal.verify', compact('participant'));
        }

        // senão -> mostra tela de criar senha
        return view('amigo-secreto.reveal.form', compact('participant', 'autofillKey'));
    }

    // POST /reveal/{participant}  (criar primeira senha)
    public function store(Request $request, Participant $participant)
    {
         $request->validate([
            'secret_key' => 'required|string',
            'password'   => 'required|min:4|max:10',
        ]);

        AccessHelper::log($participant->id);

         // 🔐 Confere a palavra-chave
        if (!hash_equals(trim($participant->secret_key ?? ''), trim($request->secret_key))) {
            \Log::warning("[REVEAL] Palavra-chave incorreta", [
                'participant_id' => $participant->id,
            ]);
            return back()->withErrors(['secret_key' => 'Palavra-chave incorreta.']);
        }


        // só cria se ainda não existir
        $exists = SecretReveal::where('participant_id', $participant->id)->exists();
        if ($exists) {
            return redirect()
                ->route('reveal.form', $participant)
                ->with('error', 'Você já tem uma senha. Use-a para revelar.');
        }

        SecretReveal::create([
            'participant_id' => $participant->id,
            'password_hash'  => Hash::make($request->password),
            'revealed_at'    => now(),
        ]);

        $request->session()->put('legacy_reveal.participant_id', $participant->id);

        return redirect()->route('reveal.result', $participant)
                     ->with('success', 'Senha criada com sucesso!');
    }

    // POST /reveal/verify/{participant}  (validar senha existente)
    public function verify(Request $request, Participant $participant)
    {
        $request->validate(['password' => 'required']);

        AccessHelper::log($participant->id);

        $participant->update([
            'last_access_at' => now()
        ]);

        $reveal = SecretReveal::where('participant_id', $participant->id)->first();

        if (!$reveal || !Hash::check($request->password, $reveal->password_hash)) {
            return back()->withErrors(['password' => 'Senha incorreta.']);
        }

        // 📌 Registrar somente o último acesso, sem alterar revealed_at
        $reveal->update(['last_access_at' => now()]);
        $request->session()->put('legacy_reveal.participant_id', $participant->id);

        return redirect()->route('reveal.result', $participant);
    }

    // GET /reveal/result/{participant}
    public function result(Participant $participant, Request $request)
    {
        if ($request->session()->get('legacy_reveal.participant_id') !== $participant->id) {
            return redirect()->route('reveal.form', $participant);
        }

        $assignment = $participant->givenAssignments()->with('receiver')->first();

        AccessHelper::log($participant->id);

        $participant->update([
            'last_access_at' => now()
        ]);

        return response()->view('amigo-secreto.reveal.result', compact('participant', 'assignment'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function listRevealed(Group $group)
    {
        $revealed = SecretReveal::whereNotNull('revealed_at')
            ->with(['participant' => function ($query) {
                $query->select('id', 'name', 'email', 'whatsapp', 'group_id');
            }])
            ->whereHas('participant', function ($q) use ($group) {
                $q->where('group_id', $group->id);
            })
            ->get();

        return view('amigo-secreto.reveal.list', compact('revealed', 'group'));
    }

    // public function accessByKey($secret_key)
    // {
    //     $participant = Participant::where('secret_key', $secret_key)->firstOrFail();

    //     return redirect()->route('reveal.form', $participant);
    // }

    // public function accessByKey($id)
    // {
    //     $participant = Participant::findOrFail($id);

    //     // 🔗 URL secreta para revelar
    //     $url = route('reveal.by.key', ['key' => $participant->secret_key]);

    //     // 🎨 QR personalizado
    //     $qrCode = QrCode::create($url)
    //         ->setEncoding(new Encoding('UTF-8'))
    //         ->setSize(280)
    //         ->setMargin(10)
    //         ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
    //         ->setForegroundColor(new Color(79, 70, 229))       // Roxo do site
    //         ->setBackgroundColor(new Color(255, 255, 255));     // Branco

    //     // 🖨 Gerar imagem PNG
    //     $writer = new PngWriter();
    //     $result = $writer->write($qrCode);

    //     // 📌 Conversão para Base64
    //     $qrBase64 = base64_encode($result->getString());

    //     return view('amigo-secreto.participants.qr', compact('participant', 'qrBase64'));
    // }

    public function showBySecretKey($key)
    {
        AccessHelper::log();
        $participant = Participant::where('secret_key', $key)->firstOrFail();

        return redirect()->route('reveal.form', [
            'participant' => $participant->id,
            'autofill_key' => $key // 👈 Passa a chave automaticamente
        ]);
    }
}
