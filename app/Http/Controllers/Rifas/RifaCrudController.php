<?php

namespace App\Http\Controllers\Rifas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rifas\Rifa;
use App\Models\Rifas\RifaNome;
use Carbon\Carbon;

class RifaCrudController extends Controller
{
    public function index()
    {
        $rifas = Rifa::orderBy('id','desc')->get();
        return view('rifas.crud.index', compact('rifas'));
    }

    public function create()
    {
        return view('rifas.crud.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required',
            'premio' => 'required',
            'valor' => 'required|numeric',
        ]);

        Rifa::create($request->all());

        return redirect()->route('rifas.index')->with('success','Rifa criada!');
    }

    public function show(Rifa $rifa)
    {
        return view('rifas.crud.show', compact('rifa'));
    }

    /* --------------------------------------------------------------- *
     * GERAÇÃO DE NOMES DA CARTELA
     * --------------------------------------------------------------- */
    public function gerarNomesForm(Rifa $rifa)
    {
        return view('rifas.crud.gerar-nomes', compact('rifa'));
    }

    public function gerarNomes(Request $request, Rifa $rifa)
    {
        $request->validate([
            'quantidade' => 'required|integer|min:1|max:500',
        ]);

        $faker = \Faker\Factory::create('pt_BR');

        for ($i = 1; $i <= $request->quantidade; $i++) {

            $nomeCartela = mb_strtoupper(
                $faker->firstNameFemale(),
                'UTF-8'
            );

            $status = collect(['disponivel','reservado','pago'])->random();

            $comprador = $status !== 'disponivel'
                ? $faker->firstName()
                : null;

            RifaNome::create([
                'rifa_id'            => $rifa->id,
                'nome'               => $nomeCartela,
                'status'             => $status,
                'comprador_nome'     => $comprador,
                'comprador_whatsapp' =>
                    $comprador ? '11' . $faker->numerify('9########') : null,
                'comprado_em'        =>
                    $comprador ? Carbon::now()->subDays(rand(0,5)) : null,
            ]);
        }

        return redirect()
            ->route('rifas.show', $rifa->id)
            ->with('success', "Gerado(s) {$request->quantidade} nomes!");
    }

     public function edit(Rifa $rifa)
    {
        return view('rifas.crud.edit', compact('rifa'));
    }

    public function update(Request $request, Rifa $rifa)
    {
        $request->validate([
            'titulo' => 'required',
            'premio' => 'required',
            'valor'  => 'required|numeric',
        ]);

        $rifa->update($request->all());

        return redirect()->route('rifas.index')->with('success','Rifa atualizada!');
    }

     public function destroy(Rifa $rifa)
    {
        $rifa->delete();
        return back()->with('success','Rifa removida');
    }
}
