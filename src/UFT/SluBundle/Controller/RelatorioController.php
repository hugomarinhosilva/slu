<?php

namespace UFT\SluBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use UFT\SluBundle\Entity\PessoaLdap;
use UFT\SluBundle\Entity\SieAluno;
use UFT\SluBundle\Entity\SieServidor;
use UFT\SluBundle\Form\TipoRelatorioType;

/**
 * Relatorio controller.
 *
 * @Route("/relatorio")
 */
class RelatorioController extends Controller
{
    /**
     * @Route("/", name="novo_relatorio")
     * @Security("has_role('ROLE_LISTA_EMAIL')")
     */
    public function verificacaoAction(Request $request)
    {
        $filtro = array();
        $emDB2 = $this->getDoctrine()->getManager('db2');
//        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(
            TipoRelatorioType::class,
            $filtro,
            array(
                'action' => $this->generateUrl('exibir_relatorio'),
                'method' => 'POST',
            )
        );

        return $this->render(
            '@Slu/Relatorio/index.html.twig',
            array(
                'data' => $filtro,
                'form' => $form->createView(),
                'titulo' => 'Relatórios',
                'rota_cancelar' => 'homepage',
            )
        );
    }


    /**
     * @Route("/exibir_alunos", name="exibir_relatorio_alunos")
     * @Security("has_role('ROLE_LISTA_EMAIL')")
     */
    public function exibirRelatorioAlunosAction(Request $request)
    {

        $query = ["tipo" => "5", "departmentNumber" => "1.00.00.00.00.00.00.00"];

        $em2 = $this->getDoctrine()->getManager('db2');

                $pessoas = $em2->getRepository(SieAluno::class)->createQueryBuilder('a')
                    ->select("a.nomeCampus ,a.nomeCurso, a.nome,a.descricaoSituacao ,CASE WHEN a.email like '%@mail.uft%' OR a.email like '%@uft%' THEN a.email ELSE a.email2 END as email2")
                    ->where("a.idSituacao = 1")
                    ->andWhere("a.email2 is not null OR a.email like '%@mail.uft%' OR a.email like '%@uft%'")
                    ->distinct()
                    ->orderBy('a.nomeCampus, a.nomeCurso, a.nome')
                    ->getQuery()->getResult();

        return $this->render(
            '@Slu/Relatorio/listagem_alunos.html.twig',
            array(
                'data' => $pessoas,
                'query' => $query,
            )
        );
    }

    /**
     * @Route("/exibir", name="exibir_relatorio")
     * @Security("has_role('ROLE_LISTA_EMAIL')")
     */
    public function exibirRelatorioAction(Request $request)
    {
        $query = $request->request->get('tipo_relatorio');
        $em2 = $this->getDoctrine()->getManager('db2');
        $em = $this->getDoctrine()->getManager();
//

        if(!isset($query['departmentNumber'])){
            $query['departmentNumber'] = rtrim('1.00.00.00.00.00.00.00', '.00') . '%';
        }

        $filtro = $em->getRepository('UserBundle:FiltroUnidade')->findOneByCodEstruturado($query['departmentNumber']);
        $departmentNumber = rtrim($query['departmentNumber'], '.00') . '%';

        switch ($query['tipo']) {
            case '1' :
                $pessoas = $em2->getRepository(SieAluno::class)->createQueryBuilder('a')
                    ->select("a.nome,a.descricaoSituacao ,CASE WHEN a.email like '%@mail.uft%' OR a.email like '%@uft%' THEN a.email ELSE a.email2 END as email2")
                    ->where('a.codEstruturadoExercicio like :departmentNumber')
                    ->andWhere("a.email2 is not null OR a.email like '%@mail.uft%' OR a.email like '%@uft%'")
                    ->setParameter('departmentNumber', $departmentNumber)
                    ->distinct()
                    ->orderBy('a.nome')
                    ->getQuery()->getResult();
                break;
            case '2' :
                $pessoas = $em2->getRepository(SieServidor::class)->createQueryBuilder('a')
                    ->select("a.nome,a.descricaoSituacao ,CASE WHEN a.email like '%@mail.uft%' OR a.email like '%@uft%' THEN a.email ELSE a.email2 END as email2")
                    ->where('a.codEstruturadoExercicio like :departmentNumber')
                    ->andWhere("a.email2 is not null OR a.email like '%@mail.uft%' OR a.email like '%@uft%'")
                    ->andWhere('a.idCargo = :cargo1 OR a.idCargo = :cargo2 OR a.idCargo = :cargo3')
                    ->andWhere('a.idSituacao <> :idSituacao')
                    ->setParameter('departmentNumber', $departmentNumber)
                    ->setParameter('cargo1', 61)
                    ->setParameter('cargo2', 62)
                    ->setParameter('cargo3', 733)
                    ->setParameter('idSituacao', 5)
                    ->distinct()
                    ->orderBy('a.nome')
                    ->getQuery()->getResult();
                break;
            case '3' :
                $pessoas = $em2->getRepository(SieServidor::class)->createQueryBuilder('a')
                    ->select("a.nome,a.descricaoSituacao , CASE WHEN a.email like '%@mail.uft%' OR a.email like '%@uft%' THEN a.email ELSE a.email2 END as email2")
                    ->where('a.codEstruturadoExercicio like :departmentNumber')
                    ->andWhere("a.email2 is not null OR a.email like '%@mail.uft%' OR a.email like '%@uft%'")
                    ->andWhere('a.idCargo <> :cargo1 OR a.idCargo <> :cargo2 OR a.idCargo <> :cargo3')
                    ->andWhere('a.idSituacao <> :idSituacao')
                    ->setParameter('departmentNumber', $departmentNumber)
                    ->setParameter('cargo1', 61)
                    ->setParameter('cargo2', 62)
                    ->setParameter('cargo3', 733)
                    ->setParameter('idSituacao', 5)
                    ->distinct()
                    ->orderBy('a.nome')
                    ->getQuery()->getResult();
                break;
            default :
                $alunos = $em2->getRepository(SieAluno::class)->createQueryBuilder('a')
                    ->select("a.nome,a.descricaoSituacao ,CASE WHEN a.email like '%@mail.uft%' OR a.email like '%@uft%' THEN a.email ELSE a.email2 END as email2")
                    ->where('a.codEstruturadoExercicio like :departmentNumber')
                    ->andWhere("a.email2 is not null OR a.email like '%@mail.uft%' OR a.email like '%@uft%'")
                    ->setParameter('departmentNumber', $departmentNumber)
                    ->distinct()
                    ->orderBy('a.nome')
                    ->getQuery()->getResult();
                $servidores = $em2->getRepository(SieServidor::class)->createQueryBuilder('a')
                    ->select("a.nome,a.descricaoSituacao ,CASE WHEN a.email like '%@mail.uft%' OR a.email like '%@uft%' THEN a.email ELSE a.email2 END as email2")
                    ->where('a.codEstruturadoExercicio like :departmentNumber')
                    ->andWhere("a.email2 is not null OR a.email like '%@mail.uft%' OR a.email like '%@uft%'")
                    ->setParameter('departmentNumber', $departmentNumber)
                    ->distinct()
                    ->orderBy('a.nome')
                    ->getQuery()->getResult();
                $pessoas  = array_merge($servidores,$alunos);
                break;


        }


        return $this->render(
            '@Slu/Relatorio/listagem.html.twig',
            array(
                'data' => $pessoas,
                'filtro' => $filtro,
                'query' => $query,
            )
        );
    }
    /**
     * @Route("/baixar", name="baixar_relatorio")
     * @Security("has_role('ROLE_LISTA_EMAIL')")
     */
    public function downloadAction(Request $request)
    {

        $relatorioSql = $this->get('slu.utils.relatoriosql');
        $query = $request->query->get('query');
        $relatorioSql->setOptions($query);


        // Gerar relatório
        if (!empty($query) and $query['tipo']=="5"){
            $relatorio = $relatorioSql->generateRelatorio(0, false, true);

        }
        else{
            $relatorio = $relatorioSql->generateRelatorio(0);

        }

        $options = array();

        if (isset($relatorio['colunas'])) {
            // Renomear as colunas da planilhas
            $options['heading_columns']   = $relatorio['colunas'];
            // Ocultar as colunas que não foram renomeadas utilizando o filtro de colunas
            $options['filter_columns']    = array_keys($relatorio['colunas']);
        }


        // Nomear arquivo
        $options['filename'] = $relatorio['nome'];


        return $relatorioSql->generateFile($relatorio['data'], $options);
    }

}
