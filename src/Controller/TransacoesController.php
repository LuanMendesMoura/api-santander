<?php

namespace App\Controller;

use App\Dto\TransacaoRealizarDto;
use App\Repository\ContaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class TransacoesController extends AbstractController
{
    #[Route('/transacoes', name: 'transacoes_realizar', methods: ['POST'])]
    public function realizar(
        #[MapRequestPayload(acceptFormat: 'json')]
        TransacaoRealizarDto $entrada,

        ContaRepository $contaRepository,
    ): JsonResponse
    {
        $erros = [];
        // validar os dados do DTO de entrada
        if (!$entrada->getIdUsuarioOrigem()) {
            array_push($erros, [
                'message' => 'Não existe esse usuário origem'
           ]);
        }
        if (!$entrada->getIdUsuarioDestino()) {
            array_push($erros, [
                'message' => 'Não existe esse usuário destino'
           ]);
        }
        if (!$entrada->getValor()){
            array_push($erros, [
                'message' => 'Valor é obrigatório'
           ]);
        }
        if ((float) $entrada->getValor() <= 0 ){
            array_push($erros, [
                'message' => 'Valor deve ser maior que zero!'
           ]);
        }

        // validar se as contas sao iguais
        if ($entrada->getIdUsuarioDestino() === $entrada->getIdUsuarioOrigem()){
            array_push($erros, [
                'message' => 'As contas devem ser distintas'
           ]);
        }

        if (count($erros) > 0) {
            return $this->json($erros, 422);
        }

        // validar se as contas existem
        $contaOrigem = $contaRepository->findByUsuarioId($entrada->getIdUsuarioOrigem());

        if (!$contaOrigem){
            return $this->json([
                'message' => 'A conta origem não existe'
           ], 404);
        }

        $contaDestino = $contaRepository->findByUsuarioId($entrada->getIdUsuarioDestino());
        if (!$contaDestino){
            return $this->json([
                'message' => 'A conta destino não existe'
           ], 404);
        }

        // validar se a origem tem saldo suficiente
        $saldoContaOrigem = (float)$contaOrigem->getSaldo();
        $transacaoValor = (float)$entrada->getValor();
        if (($saldoContaOrigem - $transacaoValor) >= 0){
            return $this->json([
                'message' => 'Saldo insuficiente'
            ], 404);
        } 
        
        dd($erros);
    }
}
