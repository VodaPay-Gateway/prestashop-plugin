<?php

use VodaPay\Config\Config;

class AdminVodaPayReportsController extends AdminController
{
    /**
     * function __construct
     *
     * @return void
     */

    public function __construct()
    {
        $this->bootstrap      = true;
        $this->table          = 'vodapay_online_payment';
        $this->lang           = false;
        $this->explicitSelect = false;
        $this->allow_export   = false;
        $this->deleted        = false;
        $this->_orderBy       = 'nid';
        $this->_orderWay      = 'DESC';
        $this->list_no_link   = true;
        $config               = new Config();

        parent::__construct();
        $this->_use_found_rows = false;

        $status = $config->getOrderStatus();
        $label  = $config->getOrderStatusLabel();

        $this->statusArr = [
            $status . '_PENDING'            => $label . ' Pending',
            $status . '_AWAIT_3DS'          => $label . ' Await 3DS',
            $status . '_PROCESSING'         => $label . ' Processing',
            $status . '_FAILED'             => $label . ' Failed',
            $status . '_DECLINED'           => $label . ' Declined',
            $status . '_COMPLETE'           => $label . ' Complete',
            $status . '_AUTHORISED'         => $label . ' Authorised',
            $status . '_FULLY_CAPTURED'     => $label . ' Fully Captured',
            $status . '_AUTH_REVERSED'      => $label . ' Auth Reversed',
            $status . '_FULLY_REFUNDED'     => $label . ' Fully Refunded',
            $status . '_PARTIALLY_REFUNDED' => $label . ' Partially Refunded'
        ];

        $this->fields_list = array(
            'id_order' => array(
                'title'   => $this->trans('Id', array(), 'Admin.Global'),
                'orderby' => false,

            ),

            'amount' => array(
                'title'    => $this->trans('Amount', array(), 'Admin.Global'),
                'callback' => 'setOrderCurrency',
                'orderby'  => false,
            ),

            'reference' => array(
                'title'   => $this->trans('Reference', array(), 'Admin.Global'),
                'orderby' => false,
            ),

            'action' => array(
                'title'   => $this->trans('Action', array(), 'Admin.Global'),
                'orderby' => false,
            ),

            'state' => array(
                'title'   => $this->trans('State', array(), 'Admin.Global'),
                'orderby' => false,
            ),

            'status' => array(
                'title'    => $this->trans('Status', array(), 'Admin.Global'),
                'orderby'  => false,
                'callback' => 'renderStatus',
            ),

            'capture_amt' => array(
                'title'    => $this->trans('Capture Amount', array(), 'Admin.Global'),
                'orderby'  => false,
                'callback' => 'setOrderCurrency',
            ),

            'id_payment' => array(
                'title'   => $this->trans('Payment Id', array(), 'Admin.Global'),
                'orderby' => false,
            ),
            'created_at' => array(
                'title'      => $this->trans('Date', array(), 'Admin.Global'),
                'orderby'    => false,
                'type'       => 'datetime',
                'filter_key' => 'a!created_at',
            ),
        );
    }

    /**
     * set order currency.
     *
     * @param array $echo
     * @param string $tr
     *
     * @return string
     */
    public static function setOrderCurrency(array $echo, string $tr): string
    {
        $order = new \Order($tr['id_order']);

        return \Tools::displayPrice($echo, (int)$order->id_currency);
    }

    /**
     * Render List.
     *
     * @return object
     */
    public function renderList(): object
    {
        $this->_select = ' nid as  id_vodapay_online_payment';

        return parent::renderList();
    }

    /**
     * Render status.
     *
     * @param string $status
     *
     * @return string
     */
    public function renderStatus($status): string
    {
        return $this->statusArr[$status];
    }
}
