$invoice = $this->load->view('includes/email', $data, true);
				$filePath = realpath(getcwd()).DS ."temp".DS. $data['bill_data']['inv_id']. ".pdf";
				
				$this->load->library('Htmltopdfconvert');
				$mpdf = new mPDF();
				$mpdf->forcePortraitHeaders = true;
				$mpdf->WriteHTML($invoice);

				$mpdf->Output($filePath,'F');
