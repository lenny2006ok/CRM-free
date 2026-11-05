<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Plano;
use App\Models\Servidor;
use App\Services\MercadoPagoService;
use App\Services\EvolutionApiService;
use App\Models\Template;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::with('plano', 'servidor');

        if ($request->filled('busca')) {
            $busca = $request->input('busca');
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('whatsapp', 'like', "%{$busca}%")
                  ->orWhere('iptv_nome', 'like', "%{$busca}%");
            });
        }

        if ($request->filled('status')) {
            $hoje = Carbon::today();
            if ($request->status === 'ativo') {
                $query->where('vencimento', '>=', $hoje);
            } elseif ($request->status === 'vencido') {
                $query->where('vencimento', '<', $hoje);
            }
        }

        $clientes = $query->orderBy('vencimento', 'asc')->paginate(15);
        $planos = Plano::all();
        $servidores = Servidor::all();

        return view('clientes.index', compact('clientes', 'planos', 'servidores'));
    }

    public function create()
    {
        $planos = Plano::all();
        $servidores = Servidor::all();
        return view('clientes.create', compact('planos', 'servidores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:30',
            'vencimento' => 'required|date',
            'plano_id' => 'nullable|exists:planos,id',
            'servidor_id' => 'nullable|exists:servidores,id',
            'numero_de_telas' => 'integer|min:1',
            'iptv_nome' => 'nullable|string|max:255',
            'iptv_senha' => 'nullable|string|max:255',
            'mac' => 'nullable|string|max:255',
            'notificacoes' => 'boolean',
            'notas' => 'nullable|string',
        ]);

        $user = Auth::user();

        if ($user->clientes()->count() >= $user->limite) {
            return back()->with('error', "Limite de {$user->limite} clientes atingido. Atualize seu plano de revenda.");
        }

        $validated['user_id'] = $user->id;
        $validated['role_id'] = 3;
        $validated['notificacoes'] = $request->has('notificacoes');

        $cliente = Cliente::create($validated);

        if ($request->filled('enviar_boas_vindas')) {
            $template = Template::where('finalidade', 'boas_vindas')->first();
            if ($template) {
                $msg = $template->renderizar([
                    'nome' => $cliente->nome,
                    'vencimento' => $cliente->vencimento->format('d/m/Y'),
                    'iptv_nome' => $cliente->iptv_nome ?? 'Não informado',
                    'iptv_senha' => $cliente->iptv_senha ?? 'Não informado',
                ]);
                EvolutionApiService::enviarTexto($user, $cliente->whatsapp, $msg);
            }
        }

        return redirect()->route('clientes.index')->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function edit(Cliente $cliente)
    {
        $planos = Plano::all();
        $servidores = Servidor::all();
        return view('clientes.edit', compact('cliente', 'planos', 'servidores'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:30',
            'vencimento' => 'required|date',
            'plano_id' => 'nullable|exists:planos,id',
            'servidor_id' => 'nullable|exists:servidores,id',
            'numero_de_telas' => 'integer|min:1',
            'iptv_nome' => 'nullable|string|max:255',
            'iptv_senha' => 'nullable|string|max:255',
            'mac' => 'nullable|string|max:255',
            'notificacoes' => 'boolean',
            'notas' => 'nullable|string',
        ]);

        $validated['notificacoes'] = $request->has('notificacoes');
        $cliente->update($validated);

        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente removido com sucesso!');
    }

    public function renovarManual(Cliente $cliente)
    {
        $dias = $cliente->plano ? $cliente->plano->duracao : 30;
        $base = ($cliente->vencimento && $cliente->vencimento->isFuture()) ? $cliente->vencimento : Carbon::today();
        $cliente->vencimento = $base->copy()->addDays($dias);
        $cliente->save();

        return back()->with('success', "Cliente renovado com sucesso até {$cliente->vencimento->format('d/m/Y')}!");
    }

    public function enviarPix(Cliente $cliente)
    {
        if (!$cliente->plano) {
            return back()->with('error', 'O cliente não possui um plano associado para gerar Pix.');
        }

        $pagamento = MercadoPagoService::criarPix($cliente, $cliente->plano, Auth::user());
        $linkPix = $pagamento->ticket_url ?: url('/pagamento/' . $pagamento->mercado_pago_id);
        $textoPix = "Olá, {$cliente->nome}! Aqui está o seu link de renovação Pix no valor de R$ " . number_format((float) $pagamento->valor, 2, ',', '.') . ":\n\n*Código Pix:* {$pagamento->qr_code}\n\n*Link Direto:* {$linkPix}";

        EvolutionApiService::enviarTexto(Auth::user(), $cliente->whatsapp, $textoPix);

        return back()->with('success', 'Cobrança Pix gerada e enviada para o WhatsApp do cliente!');
    }
}
