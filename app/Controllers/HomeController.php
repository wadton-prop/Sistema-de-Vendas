<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\ClienteRepository;
use App\Repositories\ProdutoRepository;
use App\Repositories\VendaRepository;

class HomeController extends Controller
{
    public function index(): void
    {
        $hoje = date('Y-m-d');
        $vendas = new VendaRepository();

        $this->view('home/index', [
            'titulo'        => 'Início',
            'totalClientes' => (new ClienteRepository())->contar(),
            'totalProdutos' => (new ProdutoRepository())->contar(),
            'estoqueBaixo'  => (new ProdutoRepository())->contarEstoqueBaixo(),
            'resumoHoje'    => $vendas->resumo(['data_inicio' => $hoje, 'data_fim' => $hoje]),
        ]);
    }
}
