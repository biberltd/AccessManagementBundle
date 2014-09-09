<?php
/**
 * sys.tr.php
 *
 * Bu dosya ilgili paketin sistem (hata ve başarı) mesajlarını Türkçe olarak barındırır.
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\AccessManagementBundle
 * @subpackage	Resources
 * @name	    sys.tr.php
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
    'err'       => array(
        /** Access Management Model */
        'amm'   => array(
            'unknown'                   => 'Bilinmeyen bir hata oluştu veya AccessManagementModel objesi yaratılamadı.',
        ),
    ),
  /** Başarı mesajları */
    'scc'       => array(
        /** Access Management Model */
        'amm'   => array(
            'default'                   => 'Veri tabanına gönderilen işlem başarıyla tamamlandı.'
        ),
    ),
);
/**
 * Change Log / Değişiklik Kaydı
 * **************************************
 * v1.0.0                      Can Berkol
 * 02.08.2013
 * **************************************
 * A err
 * A err.amm
 * A err.amm.unknown
 * A scc
 * A scc.amm
 * A scc.amm.default
 */