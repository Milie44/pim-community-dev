<?php
namespace Strixos\IcecatConnectorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Action\RowAction;

use \Exception;

/**
 * Icecat product controller regroups all features for products entities (import and list)
 * 
 * @author    Romain Monceau @ Akeneo
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductController extends Controller
{
    /**
     * Load only products identifiers from icecat to local database
     * 
     * @Route("/product/load-from-icecat")
     * @Template()
     */
    public function loadFromIcecatAction()
    {
        try {
            $srvConnector = $this->container->get('akeneo.connector.icecat_service');
            $srvConnector->importProducts();
            $this->get('session')->setFlash('notice', 'Base products has been imported from Icecat');
        } catch (Exception $e) {
            $this->get('session')->setFlash('exception', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('strixos_icecatconnector_product_list'));
    }

    /**
     * List Icecat products in a grid
     * 
     * @Route("/product/list")
     * @Template()
     */
    public function listAction()
    {
        // creates simple grid based on entity (ORM)
        $source = new GridEntity('StrixosIcecatConnectorBundle:SourceProduct');
        $grid = $this->get('grid');
        $grid->setSource($source);
        // add an action column to load import of all products of a supplier
        $rowAction = new RowAction('Import product to PIM', 'strixos_icecatconnector_product_loadproduct');
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);
        // manage the grid redirection, exports response of the controller
        return $grid->getGridResponse('StrixosIcecatConnectorBundle:Product:grid.html.twig');
    }

    /**
     * Load all icecat product data to local database
     * 
     * @Route("/product/load-product/{id}")
     * @Template()
     */
    public function loadProductAction($id)
    {
        try {
            $srvConnector = $this->container->get('akeneo.connector.icecat_service');
            $srvConnector->importProductFromIcecatXml($id);
            $product = $this->getDoctrine()->getRepository('StrixosIcecatConnectorBundle:SourceProduct')->find($id);
            
            // Prepare notice message
            $viewRenderer = $this->render('StrixosIcecatConnectorBundle:Product:loadProduct.html.twig', 
                    array('product' => $product));
            $this->get('session')->setFlash('notice', $viewRenderer->getContent());
        } catch (Exception $e) {
            $this->get('session')->setFlash('exception', $e->getMessage());
        }
        
        // Redirect to products list
        return $this->redirect($this->generateUrl('strixos_icecatconnector_product_list'));
    }
}