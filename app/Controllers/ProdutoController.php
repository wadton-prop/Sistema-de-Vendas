<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Models\Produto;
use App\Repositories\ProdutoRepository;
use PDOException;

/**
 * CRUD de Produtos — é o que o PDV original já fazia
 * (index.html + salvar_produto.php + pdv.php + editar_produto.php),
 * agora reorganizado em MVC.
 */
class ProdutoController extends Controller
{
    private const EXTENSOES = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    private ProdutoRepository $repo;

    public function __construct()
    {
        $this->repo = new ProdutoRepository();
    }

    public function index(): void
    {
        $busca = (string) $this->input('busca', '');
        $this->view('produtos/index', [
            'titulo'   => 'Produtos',
            'busca'    => $busca,
            'produtos' => $this->repo->listar($busca),
        ]);
    }

    public function criar(): void
    {
        $this->view('produtos/form', ['titulo' => 'Novo produto', 'produto' => new Produto(), 'erros' => []]);
    }

    public function salvar(): void
    {
        $this->exigirPost('produtos/index');
        $produto = Produto::fromArray($_POST);
        $produto->id = null;

        $erros = $produto->validar();
        $imagem = $this->processarUpload($erros);
        if ($erros) {
            $this->view('produtos/form', ['titulo' => 'Novo produto', 'produto' => $produto, 'erros' => $erros]);
            return;
        }

        $produto->imagem = $imagem;
        $this->repo->inserir($produto);
        Flash::sucesso("Produto \"{$produto->nome}\" cadastrado com sucesso!");
        $this->redirecionar('produtos/index');
    }

    public function editar(): void
    {
        $produto = $this->repo->buscarPorId($this->inputInt('id'));
        if (!$produto) {
            Flash::erro('Produto não encontrado.');
            $this->redirecionar('produtos/index');
        }
        $this->view('produtos/form', ['titulo' => 'Editar produto', 'produto' => $produto, 'erros' => []]);
    }

    public function atualizar(): void
    {
        $this->exigirPost('produtos/index');
        $existente = $this->repo->buscarPorId($this->inputInt('id'));
        if (!$existente) {
            Flash::erro('Produto não encontrado.');
            $this->redirecionar('produtos/index');
        }

        $produto = Produto::fromArray($_POST);
        $produto->imagem = $existente->imagem;

        $erros = $produto->validar();
        $novaImagem = $this->processarUpload($erros);
        if ($erros) {
            $this->view('produtos/form', ['titulo' => 'Editar produto', 'produto' => $produto, 'erros' => $erros]);
            return;
        }

        if ($novaImagem !== null) {
            $produto->imagem = $novaImagem;
        }
        $this->repo->atualizar($produto);
        Flash::sucesso("Produto \"{$produto->nome}\" atualizado com sucesso!");
        $this->redirecionar('produtos/index');
    }

    public function excluir(): void
    {
        $this->exigirPost('produtos/index');
        $produto = $this->repo->buscarPorId($this->inputInt('id'));
        if (!$produto) {
            Flash::erro('Produto não encontrado.');
            $this->redirecionar('produtos/index');
        }

        try {
            $this->repo->excluir((int) $produto->id);
            Flash::sucesso("Produto \"{$produto->nome}\" excluído.");
        } catch (PDOException $e) {
            Flash::erro('Não foi possível excluir o produto: ' . $e->getMessage());
        }
        $this->redirecionar('produtos/index');
    }

    /**
     * Faz o upload da imagem com nome único e extensão validada
     * (no original o nome do arquivo do usuário era usado direto, o que permitia
     * sobrescrever arquivos ou enviar .php).
     */
    private function processarUpload(array &$erros): ?string
    {
        $arquivo = $_FILES['imagem'] ?? null;
        if (!$arquivo || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            $erros[] = 'Falha no upload da imagem (código ' . $arquivo['error'] . ').';
            return null;
        }

        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, self::EXTENSOES, true) || @getimagesize($arquivo['tmp_name']) === false) {
            $erros[] = 'A imagem deve ser JPG, PNG, GIF ou WEBP.';
            return null;
        }
        if ($erros) {
            return null; // não salva o arquivo se o formulário tem outros erros
        }

        $nome = 'upload_' . bin2hex(random_bytes(8)) . '.' . $extensao;
        if (!move_uploaded_file($arquivo['tmp_name'], PUBLIC_PATH . '/imagens/' . $nome)) {
            $erros[] = 'Não foi possível salvar a imagem no servidor (verifique a permissão da pasta public/imagens).';
            return null;
        }
        return $nome;
    }
}
