<?php
// src/SisuBundle/Utils/RelatorioSQL.php
namespace UFT\SluBundle\Util;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use UFT\SluBundle\Entity\SieAluno;
use UFT\SluBundle\Entity\SieServidor;

class RelatorioSQL
{

    private $em;

    private $container;
    private $multiplo;
    private $options;

    private $relatorios = array(
        array(
        'nome' => 'Lista de e-mails',
        'descricao' => 'Lista de e-mails do slu.',
        'sql' => '1',
        'view' => array(
            'Versão 1 - Expandido' => 'listagem',
        ),
        'colunas' => array(
            'nome' => 'NOME',
            'email2' => 'E-MAIL',
        ),
        'agrupamento' => array(''),
        ),
    );

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->multiplo = 5;
        $this->container = $container;

    }

    /**
     * Retorna um array contendo os valores da propriedade $relatorios
     *
     * @param  boolean $modo_detalhado
     * @return array
     */
    public function getRelatorios($modo_detalhado = true)
    {
        if (!$modo_detalhado) {
            $relatorios = array();
            foreach ($this->relatorios as $key => $value) {
                $relatorios[$key] = $value['nome'];
            }
            return $relatorios;
        }
        return $this->relatorios;
    }

    public function generateRelatorio($id, $group_data = false, $col_alunos = false)
    {
        if (isset($this->relatorios[$id])) {
            $relatorio['id'] = ($id);
            $relatorio['nome'] = $this->relatorios[$id]['nome'];
            $relatorio['descricao'] = isset($this->relatorios[$id]['descricao']) ? $this->relatorios[$id]['descricao'] : '';
            $relatorio['data'] = $this->__executeSQL($this->getOptions()['tipo'],$this->getOptions()['departmentNumber']);

            $relatorio['view'] = $this->relatorios[$id]['view'];

            // Utilizado somente para geração do arquivo Excel
            if ($col_alunos){
                $relatorio['colunas'] = ["nomeCampus"=> "CÂMPUS", "nomeCurso"=>"Curso","nome" => "NOME", "email2" => "E-MAIL"];
            }else{

                $relatorio['colunas'] = $this->relatorios[$id]['colunas'];
            }

            // Formatando o conteúdo do relatório em um array associativo
            if ($group_data == true && isset($this->relatorios[$id]['agrupamento'])) {
                $relatorio['data'] = $this->__groupData($relatorio['data'], $this->relatorios[$id]['agrupamento']);
            }
            return $relatorio;
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }




    private function __executeSQL($tipo,$departmentNumber)
    {

        $em2 = $this->em;
        switch ($tipo) {
            case '1' :
                $pessoas = $em2->getRepository(SieAluno::class)->createQueryBuilder('a')
                    ->select("a.nome,CASE WHEN a.email like '%@mail.uft%' OR a.email like '%@uft%' THEN a.email ELSE a.email2 END as email2")
                    ->where('a.codEstruturadoExercicio like :departmentNumber')
                    ->andWhere("a.email2 is not null OR a.email like '%@mail.uft%' OR a.email like '%@uft%'")
                    ->setParameter('departmentNumber', $departmentNumber)
                    ->distinct()
                    ->orderBy('a.nome')
                    ->getQuery()->getArrayResult();
                break;
            case '2' :
                $pessoas = $em2->getRepository(SieServidor::class)->createQueryBuilder('a')
                    ->select("a.nome,CASE WHEN a.email like '%@mail.uft%' OR a.email like '%@uft%' THEN a.email ELSE a.email2 END as email2")
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
                    ->getQuery()->getArrayResult();
                break;
            case '3' :
                $pessoas = $em2->getRepository(SieServidor::class)->createQueryBuilder('a')
                    ->select("a.nome,CASE WHEN a.email like '%@mail.uft%' OR a.email like '%@uft%' THEN a.email ELSE a.email2 END as email2")
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
                    ->getQuery()->getArrayResult();
                break;
            case '5':
                $pessoas = $em2->getRepository(SieAluno::class)->createQueryBuilder('a')
                    ->select("a.nomeCampus ,a.nomeCurso, a.nome,a.descricaoSituacao ,CASE WHEN a.email like '%@mail.uft%' OR a.email like '%@uft%' THEN a.email ELSE a.email2 END as email2")
                    ->where("a.idSituacao = 1")
                    ->andWhere("a.email2 is not null OR a.email like '%@mail.uft%' OR a.email like '%@uft%'")
                    ->distinct()
                    ->orderBy('a.nomeCampus, a.nomeCurso, a.nome')
                    ->getQuery()->getResult();
                break;
            default :


                $alunos = $em2->getRepository(SieAluno::class)->createQueryBuilder('a')
                    ->select("a.nome,CASE WHEN a.email like '%@mail.uft%' OR a.email like '%@uft%' THEN a.email ELSE a.email2 END as email2")
                    ->where('a.codEstruturadoExercicio like :departmentNumber')
                    ->andWhere("a.email2 is not null OR a.email like '%@mail.uft%' OR a.email like '%@uft%'")
                    ->setParameter('departmentNumber', $departmentNumber)
                    ->distinct()
                    ->orderBy('a.nome')
                    ->getQuery()->getArrayResult();

                $servidores = $em2->getRepository(SieServidor::class)->createQueryBuilder('a')
                    ->select("a.nome,CASE WHEN a.email like '%@mail.uft%' OR a.email like '%@uft%' THEN a.email ELSE a.email2 END as email2")
                    ->where('a.codEstruturadoExercicio like :departmentNumber')
                    ->andWhere("a.email2 is not null OR a.email like '%@mail.uft%' OR a.email like '%@uft%'")
                    ->setParameter('departmentNumber', $departmentNumber)
                    ->distinct()
                    ->orderBy('a.nome')
                    ->getQuery()->getArrayResult();

                $pessoas  = array_merge($servidores,$alunos);

                break;

        }
        return $pessoas;
    }

    private function __groupData($data, $groups)
    {
        $new_data = array();
        $cmd = '$new_data';
        foreach ($groups as $group) {
            $cmd .= "[\$row['$group']]";
        }
        $cmd .= '[] = $row;';

        foreach ($data as $row) {
            eval("$cmd");
        }
        return $new_data;
    }

    public function generateFile($data, $options = array())
    {
        if (empty($data)) {
            return null;
        }

        // $options['heading_columns'] = array(
        //   'QT_CANDIDATOS' =>'QUANT. DE CANDIDATOS',
        //   'QT_VAGAS' =>'QUANT. DE VAGAS',
        //   'QT_PREMATRICULADOS' =>'QUANT. DE INTERESSADOS',
        //   'QT_VAGAS_RESTANTES' =>'SALDO DE VAGAS',
        //   'QT_CANDIDATOS_SUPLENTES' =>'QUANT. DE SUPLENTES',
        //   'CODIGO_MODALIDADE' => 'CÓDIGO DA MODALIDADE',
        //   'MODALIDADE' => 'MODALIDADE',
        //   'CAMPUS' => 'CAMPUS',
        // );

        // $options['filter_columns'] = array(
        //   'CAMPUS', 'CURSO', 'CODIGO_MODALIDADE', 'MODALIDADE',
        //   'QT_CANDIDATOS', 'QT_CANDIDATOS_SUPLENTES', 'QT_VAGAS', 'QT_VAGAS_RESTANTES', 'QT_PREMATRICULADOS',
        // );

        if (isset($options['filename'])) {
            $filename = iconv("utf-8", "ascii//TRANSLIT", $options['filename']);
            $filename = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $filename);
            $filename = $filename . '.xls';
        } else {
            $filename = time() . '.xls';
        }

        $phpExcelObject = $this->container->get('phpexcel')->createPHPExcelObject();
        $row_number = 1;

        // Gerando o cabeçalho da planilha
        $column_number = 'a';
        foreach (array_keys($data[0]) as $value) {
            if (isset($options['filter_columns']) && !in_array($value, $options['filter_columns'])) {
                continue;
            }
            if (isset($options['heading_columns'][$value])) {
                $value = $options['heading_columns'][$value];
            }
            $cell_number = $column_number . $row_number;
            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue($cell_number, $value);
            $column_number++;
        }

        $row_number++;


        // Gerando o corpo da planilha
        foreach ($data as $row_value) {
            $column_number = 'a';
            foreach ($row_value as $column_name => $column_value) {
                if (isset($options['filter_columns']) && !in_array($column_name, $options['filter_columns'])) {
                    continue;
                }
                $cell_number = $column_number . $row_number;

                $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValue($cell_number, trim($column_value));
                $column_number++;
            }
            $row_number++;
        }


        // create the writer
        $writer = $this->container->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');

        // create the response
        $response = $this->container->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );


        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=ISO-8859-1');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

}