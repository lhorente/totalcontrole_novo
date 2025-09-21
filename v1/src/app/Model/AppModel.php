<?php
/**
 * Application model for CakePHP.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {
	function getCurrentUser() {
		// for CakePHP 2.x:
		App::uses('CakeSession', 'Model/Datasource');
		$Session = new CakeSession();
		$user = $Session->read('Auth.User');
		return $user;
	}

	function stringToSlug($texto){
		/* função que gera uma texto limpo pra virar URL:
		- limpa acentos e transforma em letra normal
		- limpa cedilha e transforma em c normal, o mesmo com o ñ
		- transforma espaços em hifen (-)
		- tira caracteres invalidos
		by Micox - elmicox.blogspot.com - www.ievolutionweb.com
		*/
		//desconvertendo do padrão entitie (tipo á para á)
		$texto = trim(html_entity_decode($texto));
		//tirando os acentos
		$texto= preg_replace('![áàãâä]+!u','a',$texto);
		$texto= preg_replace('![éèêë]+!u','e',$texto);
		$texto= preg_replace('![íìîï]+!u','i',$texto);
		$texto= preg_replace('![óòõôö]+!u','o',$texto);
		$texto= preg_replace('![úùûü]+!u','u',$texto);
		// retira caracteres especiais
		$texto= preg_replace('![,:]+!u','',$texto);
		$texto= preg_replace('[\/]','-',$texto);
		//parte que tira o cedilha e o ñ
		$texto= preg_replace('![ç]+!u','c',$texto);
		$texto= preg_replace('![ñ]+!u','n',$texto);
		//tirando outros caracteres invalidos
		$texto= preg_replace('[^a-z0-9\-]','-',$texto);
		//tirando espaços
		$texto = str_replace(' ','-',$texto);
		//trocando duplo espaço (hifen) por 1 hifen só
		$texto = str_replace('--','-',$texto);

		return strtolower($texto);
	}
	
    function trocaVirgulaPonto($valor){
        if (stripos($valor,",")){
			$valor = str_replace('.','',$valor);
            $valor = str_replace(",", ".", $valor);
        }
        return $valor;
    }
}
