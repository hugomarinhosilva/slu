<?php
/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 22/12/15
 * Time: 16:45
 */

namespace UFT\TemaBundle\EventListener;


use Avanzu\AdminThemeBundle\Event\SidebarMenuEvent;
use Symfony\Component\HttpFoundation\Request;
use UFT\TemaBundle\Model\MenuItemModel;

class MenuListener
{

    public function onSetupMenu(SidebarMenuEvent $event)
    {
        $request = $event->getRequest();

        foreach ($this->getMenu($request) as $item) {
            $event->addItem($item);
        }
    }

    protected function getMenu(Request $request)
    {
        $earg      = array();


        $rootItems = array(
            $login = new MenuItemModel('login', 'Entrar', 'fos_user_security_login', $earg, 'fa fa-sign-in','','',null),
            $dashboard = new MenuItemModel('Dashboard', 'Painel de Controle', 'homepage', $earg, 'fa fa-dashboard','','','IS_AUTHENTICATED_FULLY'),
//            $dashboard = new MenuItemModel('Dashboard', 'Dashboard', 'homepage', $earg, 'fa fa-dashboard','','','ROLE_USER'),
            $pessoas = new MenuItemModel('nova', 'Contas Pessoais', 'lista_pessoas', $earg, 'fa fa-user','','','ROLE_GERENTE_DEPARTAMENTO'),
            $departamento = new MenuItemModel('departamento', 'Contas Departamentais', 'lista_departamentos', $earg, 'fa fa-building','','','ROLE_SUPER_ADMINISTRADOR_SLU'),
            $grupos = new MenuItemModel('grupo', 'Grupos de Usuários', 'lista_grupos', $earg, 'fa fa-group','','','ROLE_SLU_GRUPO_MOSTRAR'),
            $grupoUsuarios = new MenuItemModel('usergrupo', 'Grupos Gerenciamento', 'lista_usergrupo', $earg, 'fa fa-lock','','','ROLE_SUPER_ADMINISTRADOR_SLU'),
            $meusUsuarios = new MenuItemModel('meusgrupo', 'Gerenciar Acessos', 'lista_meusgrupos', $earg, 'fa fa-pencil-square','','','ROLE_GRUPO_USUARIO_MOSTRAR'),
            $logout = new MenuItemModel('logout', 'Sair', 'lightsaml_sp.logout', $earg, 'fa fa-sign-out','','','IS_AUTHENTICATED_FULLY'),
//            $form = new MenuItemModel('forms', 'Forms', 'homepage', $earg, 'fa fa-edit'),
//            $widgets = new MenuItemModel('widgets', 'Widgets', 'homepage', $earg, 'fa fa-th', 'new'),
//            $ui = new MenuItemModel('ui-elements', 'UI Elements', '', $earg, 'fa fa-laptop')
        );

        $pessoas->addChild(new MenuItemModel('ui-elements-general', 'Consultar', 'lista_pessoas', $earg, 'fa fa-search'))
            ->addChild(new MenuItemModel('ui-elements-icons', 'Criar nova conta', 'pessoaLdap_prenova', $earg,'','','','ROLE_SLU_USUARIO_CRIAR'));
//            ->addChild(new MenuItemModel('ui-elements-icons', 'Criar conta sem vínculo', 'pessoaLdap_prenova', $earg,'','','','ROLE_SLU_USUARIO_CRIAR'));
        $departamento->addChild(new MenuItemModel('ui-elements-general', 'Consultar', 'lista_departamentos', $earg, 'fa fa-search'))
            ->addChild(new MenuItemModel('ui-elements-icons', 'Criar conta departamental', 'departamentoLdap_nova', $earg,'','','','ROLE_SLU_USUARIO_CRIAR'));
//            ->addChild(new MenuItemModel('ui-elements-icons', 'Criar departamento', 'departamentoLdap_nova', $earg,'','','','ROLE_SLU_USUARIO_CRIAR'));
        $grupos->addChild(new MenuItemModel('ui-elements-general', 'Listar', 'lista_grupos', $earg))
            ->addChild(new MenuItemModel('ui-elements-icons', 'Criar novo grupo', 'grupoLdap_nova', $earg,'','','','ROLE_SLU_GRUPO_CRIAR'));
        $grupoUsuarios->addChild(new MenuItemModel('ui-elements-general', 'Listar', 'lista_usergrupo', $earg))
            ->addChild(new MenuItemModel('ui-elements-icons', 'Criar novo grupo', 'novo_usergrupo', $earg,'','','','ROLE_GRUPO_CRIAR'));
        return $this->activateByRoute($request->get('_route'), $rootItems);

    }

    protected function activateByRoute($route, $items) {

        foreach($items as $item) { /** @var $item MenuItemModel */
            if($item->hasChildren()) {
                $this->activateByRoute($route, $item->getChildren());
            }
            else {
                if($item->getRoute() == $route) {
                    $item->setIsActive(true);
                }
            }
        }

        return $items;
    }


}