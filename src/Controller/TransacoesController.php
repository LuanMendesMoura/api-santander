<?php

namespace App\Controller;

use App\Dto\TransacaoRealizarDto;
use App\Entity\Conta;
use App\Entity\Transacao;
use App\Repository\ContaRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
        EntityManagerInterface $entityManager 
    ): Response
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
        $saldo = (float)$contaOrigem->getSaldo();
        $transacaoValor = (float)$entrada->getValor();

        if ($saldo < $transacaoValor){
            return $this->json([
                'message' => 'Saldo insuficiente'
            ], 404);
        } 

        // realizar a transacao e salvar no banco 
        $saldoDestino = (float)$contaDestino->getSaldo();

        $contaOrigem->setSaldo($saldo - $transacaoValor);
        $entityManager->persist($contaOrigem);

        $contaDestino->setSaldo($saldoDestino + $transacaoValor);
        $entityManager->persist($contaDestino);

        $transacao = new Transacao();
        $transacao->setDataHora(new DateTime());
        $transacao->setValor($entrada->getValor());
        $transacao->setContaOrigem($contaOrigem);
        $transacao->setContaDestino($contaDestino);
        $entityManager->persist($transacao);

        $entityManager->flush();

        return new Response(status: 204);
        

        dd($erros);
    }
}
