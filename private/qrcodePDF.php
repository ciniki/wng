<?php
//
// Description
// -----------
// Generate a QR code image
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function ciniki_wng_qrcodePDF(&$ciniki, $tnid, $args) {

    //
    // Load TCPDF library
    //
    require_once($ciniki['config']['ciniki.core']['lib_dir'] . '/tcpdf/tcpdf.php');

    class MYPDF extends TCPDF {
        //Page header
        public $left_margin = 18;
        public $right_margin = 18;
        public $top_margin = 15;
        public $header_title = '';
        public $header_msg = '';
        public $header_height = 0;      // The height of the image and address
        public $footer_msg = '';

        public function Header() {
            $this->Ln(10);
            $this->SetFont('helvetica', 'B', 26);
            $this->MultiCell(180, 0, $this->header_title, 0, 'C', 0, 0);
        }

        // Page footer
        public function Footer() {
            // Position at 15 mm from bottom
        } 
    }

    //
    // Start a new document
    //
    $pdf = new MYPDF('P', PDF_UNIT, 'LETTER', true, 'UTF-8', false);
    $pdf->SetMargins($pdf->left_margin, $pdf->header_height+5, $pdf->right_margin);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    $pdf->header_title = isset($args['title']) ? $args['title'] : '';
    $filename = isset($args['title']) && $args['title'] != '' ? $args['title'] : 'qrcode';

    $barcodeobj = new TCPDF2DBarcode($args['url'], 'QRCODE,H');


    $pdf->AddPage();
    $pdf->write2DBarcode($args['url'], 'QRCODE,H', 18, 50, 180, 180, [
        'border' => false,
        'padding' => 0,
        'fgcolor' => array(0,0,0),
        'bgcolor' => array(255,255,255),
        ], 'N');


    return array('stat'=>'ok', 'pdf'=>$pdf, 'filename'=>$filename);
}
?>
