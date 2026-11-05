<?php

namespace App\Http\Controllers;

use App\Models\Plano;
use App\Models\Servidor;
use App\Models\Template;
use App\Models\Campanha;
use App\Models\Pagamento;
use App\Models\Conexao;
use App\Models\CompanyDetail;
use App\Services\EvolutionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GerenciamentoController extends Controller
{
    public function planos()
    {
        $planos = Plano::withCount('clientes')->get();
        return view('planos.index', compact('planos'));
    }

    public function salvarPlano(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'preco' => 'required|numeric|min:0',
            'duracao' => 'required|integer|min:1',
            'id_qpanel' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();

        if ($request->filled('id')) {
            $plano = Plano::findOrFail($request->id);
            $plano->update($validated);
            $msg = 'Plano atualizado com sucesso!';
        } else {
            Plano::create($validated);
            $msg = 'Plano cadastrado com sucesso!';
        }

        return redirect()->route('planos.index')->with('success', $msg);
    }

    public function deletarPlano(Plano $plano)
    {
        $plano->delete();
        return redirect()->route('planos.index')->with('success', 'Plano removido com sucesso!');
    }

    public function servidores()
    {
        $servidores = Servidor::withCount('clientes')->get();
        return view('servidores.index', compact('servidores'));
    }

    public function salvarServidor(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $validated['user_id'] = Auth::id();

        if ($request->filled('id')) {
            $servidor = Servidor::findOrFail($request->id);
            $servidor->update($validated);
            $msg = 'Servidor atualizado!';
        } else {
            Servidor::create($validated);
            $msg = 'Servidor cadastrado com sucesso!';
        }

        return redirect()->route('servidores.index')->with('success', $msg);
    }

    public function deletarServidor(Servidor $servidor)
    {
        $servidor->delete();
        return redirect()->route('servidores.index')->with('success', 'Servidor removido!');
    }

    public function templates()
    {
        $templates = Template::all();
        return view('templates.index', compact('templates'));
    }

    public function salvarTemplate(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'finalidade' => 'required|string',
            'conteudo' => 'required|string',
            'tipo_mensagem' => 'required|in:texto,imagem',
            'imagem' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();

        if ($request->filled('id')) {
            $template = Template::findOrFail($request->id);
            $template->update($validated);
            $msg = 'Template atualizado!';
        } else {
            Template::create($validated);
            $msg = 'Template criado com sucesso!';
        }

        return redirect()->route('templates.index')->with('success', $msg);
    }

    public function deletarTemplate(Template $template)
    {
        $template->delete();
        return redirect()->route('templates.index')->with('success', 'Template removido com sucesso!');
    }

    public function conexaoWhatsapp()
    {
        $user = Auth::user();
        $conexao = Conexao::first();
        $company = $user->companyDetail;

        return view('whatsapp.index', compact('conexao', 'company'));
    }

    public function conectarWhatsapp(Request $request)
    {
        $user = Auth::user();
        $instanceName = 'revenda_' . $user->id;

        $conexao = Conexao::firstOrNew(['user_id' => $user->id]);
        $conexao->whatsapp = $user->whatsapp;
        $conexao->instance_name = $instanceName;

        $resCreate = EvolutionApiService::criarInstancia($user, $instanceName);
        $resQr = EvolutionApiService::obterQrCode($user, $instanceName);

        $qrcode = $resQr['qrcode'] ?? $resQr['base64'] ?? $resCreate['qrcode']['base64'] ?? null;

        $conexao->status = ($qrcode) ? 'disconnected' : 'connected';
        $conexao->conn = ($conexao->status === 'connected');
        $conexao->save();

        return response()->json([
            'status' => $conexao->status,
            'qrcode' => $qrcode,
            'message' => 'Escaneie o QR Code no seu WhatsApp.',
        ]);
    }

    public function desconectarWhatsapp()
    {
        $user = Auth::user();
        $conexao = Conexao::first();
        if ($conexao) {
            $conexao->status = 'disconnected';
            $conexao->conn = false;
            $conexao->save();
        }

        return back()->with('success', 'Sessão do WhatsApp desconectada.');
    }

    public function configuracoes()
    {
        $user = Auth::user();
        $company = CompanyDetail::firstOrCreate(
            ['user_id' => $user->id],
            ['company_name' => 'Minha Revenda IPTV', 'company_whatsapp' => $user->whatsapp]
        );

        return view('configuracoes.index', compact('company', 'user'));
    }

    public function salvarConfiguracoes(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_whatsapp' => 'required|string|max:30',
            'access_token' => 'nullable|string',
            'public_key' => 'nullable|string',
            'pix_manual' => 'nullable|string',
            'evolution_api_url' => 'nullable|string',
            'evolution_api_key' => 'nullable|string',
            'qpanel_api_url' => 'nullable|string',
            'qpanel_api_key' => 'nullable|string',
        ]);

        $company = CompanyDetail::firstOrCreate(['user_id' => $user->id]);
        $company->update(array_filter($validated, fn($v) => !is_null($v)));

        return back()->with('success', 'Configurações e credenciais salvas com segurança!');
    }

    public function campanhas()
    {
        $campanhas = Campanha::latest()->get();
        return view('campanhas.index', compact('campanhas'));
    }

    public function salvarCampanha(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'horario' => 'required',
            'data' => 'nullable|date',
            'mensagem' => 'required|string',
            'enviar_diariamente' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['enviar_diariamente'] = $request->has('enviar_diariamente');

        Campanha::create($validated);

        return redirect()->route('campanhas.index')->with('success', 'Campanha agendada com sucesso!');
    }

    public function pagamentos()
    {
        $pagamentos = Pagamento::with('cliente', 'plano')->latest()->paginate(20);
        return view('pagamentos.index', compact('pagamentos'));
    }
}
