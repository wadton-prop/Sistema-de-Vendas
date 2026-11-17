<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Cliente;
use App\Repositories\ClienteRepository;

/**
 * CRUD de Clientes (tela 1 da atividade).
 *   index   -> lista/busca (Read)
 *   criar   -> formulário novo   | salvar    -> grava (Create)
 *   editar  -> formulário edição | atualizar -> grava (Update)
 *   excluir -> remove (Delete)
 */
class ClienteController extends Controller
{
    private ClienteRepository $repo;

    public function __construct()
    {
        $this->repo = new ClienteRepository();
    }

    public function index(): void
    {
        $busca = (string) $this->input('busca', '');
        $this->view('clientes/index', [
            'titulo'   => 'Clientes',
            'busca'    => $busca,
            'clientes' => $this->repo->listar($busca),
        ]);
    }

    public function criar(): void
    {
        $this->view('clientes/form', [
            'titulo'  => 'Novo cliente',
            'cliente' => new Cliente(),
            'erros'   => [],
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPost('clientes/index');
        $cliente = Cliente::fromArray($_POST);
        $cliente->id = null;

        $erros = $this->validar($cliente);
        if ($erros) {
            $this->view('clientes/form', ['titulo' => 'Novo cliente', 'cliente' => $cliente, 'erros' => $erros]);
            return;
        }

        $this->repo->inserir($cliente);
        Flash::sucesso("Cliente \"{$cliente->nome}\" cadastrado com sucesso!");
        $this->redirecionar('clientes/index');
    }

    public function editar(): void
    {
        $cliente = $this->repo->buscarPorId($this->inputInt('id'));
        if (!$cliente) {
            Flash::erro('Cliente não encontrado.');
            $this->redirecionar('clientes/index');
        }
        $this->view('clientes/form', ['titulo' => 'Editar cliente', 'cliente' => $cliente, 'erros' => []]);
    }

    public function atualizar(): void
    {
        $this->exigirPost('clientes/index');
        if (!$this->repo->buscarPorId($this->inputInt('id'))) {
            Flash::erro('Cliente não encontrado.');
            $this->redirecionar('clientes/index');
        }

        $cliente = Cliente::fromArray($_POST);
        $erros = $this->validar($cliente);
        if ($erros) {
            $this->view('clientes/form', ['titulo' => 'Editar cliente', 'cliente' => $cliente, 'erros' => $erros]);
            return;
        }

        $this->repo->atualizar($cliente);
        Flash::sucesso("Cliente \"{$cliente->nome}\" atualizado com sucesso!");
        $this->redirecionar('clientes/index');
    }

    public function excluir(): void
    {
        $this->exigirPost('clientes/index');
        $id = $this->inputInt('id');
        $cliente = $this->repo->buscarPorId($id);

        if (!$cliente) {
            Flash::erro('Cliente não encontrado.');
        } elseif (($qtd = $this->repo->quantidadeVendas($id)) > 0) {
            Flash::erro("Não é possível excluir \"{$cliente->nome}\": ele possui {$qtd} venda(s) registrada(s).");
        } else {
            $this->repo->excluir($id);
            Flash::sucesso("Cliente \"{$cliente->nome}\" excluído.");
        }
        $this->redirecionar('clientes/index');
    }

    /** @return string[] */
    private function validar(Cliente $cliente): array
    {
        $erros = $cliente->validar();
        if (!$erros && $this->repo->cpfEmUso($cliente->cpf, $cliente->id)) {
            $erros[] = 'Já existe um cliente cadastrado com este CPF.';
        }
        return $erros;
    }
}
