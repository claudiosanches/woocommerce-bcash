<?php
/**
 * BCash Npi.
 *
 * Validates the BCash payment information using Curl.
 * Class based in https://www.bcash.com.br/desenvolvedores/integracao-retorno-automatico-loja-online.html.
 *
 * PHP Version 5
 *
 * @category BCash
 * @package BCash/Nid
 */
class BCash_Npi {

    /**
     * Curl timeout in seconds.
     * @var integer
     */
    private $_timeout = 20;

    /**
     * BCash token.
     * @var string.
     */
    protected $token;

    /**
     * @param string $token BCash user token.
     */
    function __construct( $token ) {
        $this->token = $token;
    }

    /**
     * Makes data validation.
     *
     * @return string Validation response.
     */
    public function valid() {
        $postdata  = 'transacao=' . $_POST['id_transacao'];
        $postdata .= '&status=' . $_POST['status'];
        $postdata .= '&cod_status=' . $_POST['cod_status'];
        $postdata .= '&valor_original=' . $_POST['valor_original'];
        $postdata .= '&valor_loja=' . $_POST['valor_loja'];
        $postdata .= '&token=' . $this->token;

        return $this->verify( $postdata );
    }

    /**
     * Validates the data received via curl with the BCash.
     *
     * @param  array $data order items.
     *
     * @return mixed       returns VERIFICADO or null
     */
    private function verify( $data ) {
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, 'https://www.bcash.com.br/checkout/verify/' );
        curl_setopt( $curl, CURLOPT_POST, true );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_HEADER, false );
        curl_setopt( $curl, CURLOPT_TIMEOUT, $this->_timeout );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
        $result = trim( curl_exec( $curl ) );
        curl_close( $curl );
        return $result;
    }

}
