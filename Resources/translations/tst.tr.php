<?php
/**
 * tst.tr.php
 *
 * Bu dosya ilgili paketin test mesajlarını Türkçe olarak barındırır.
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\AccessManagementBundle
 * @subpackage	Resources
 * @name	    tst.tr.php
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.0
 * @date        02.08.2013
 *
 * =============================================================================================================
 * !!! ÖNEMLİ !!!
 *
 * Çalıştığınız sunucu ortamına göre Symfony ön belleğini temizlemek için işbu dosyayı her değiştirişinizden sonra
 * aşağıdaki komutu çalıştırmalısınız veya app/cache klasörünü silmelisiniz. Aksi takdir de tercümelerde
 * yapmış olduğunuz değişiklikler işleme alıalınmayacaktır.
 *
 * $ sudo -u apache php app/console cache:clear
 * VEYA
 * $ php app/console cache:clear
 * =============================================================================================================
 * TODOs:
 * Yok
 */
/** İçiçe anahtar tanımlaması yapabilirsiniz */
return array(
    /** Hata mesajları */
    'tst'       => array(
        /** Grup: locale */
        'locale'   => array(
            'current'                   => '%locale% seçili dil kodudur.',
            'set'                       => 'Seçili dil kodunuz "%locale%" olarak değiştirilmiştir.',
        ),
    ),
);
/**
 * Change Log / Değişiklik Kaydı
 * **************************************
 * v1.0.0                      Can Berkol
 * 02.08.2013
 * **************************************
 * A tst
 * A tst.locale
 * A tst.locale.current
 * A tst.locale.set
 */