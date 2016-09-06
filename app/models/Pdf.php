<?php

class Pdf extends Model
{
	private $id;
	private $sending_id;
	private $evaluator_id;
	private $salary;
	private $new_salary;
	private $final_note;
	private $perf;
	private $bonus;
	private $bonus_prop;
	private $nr_salary_prop;
	private $perf_prop;
	private $nr_salary;
	private $comment;
	private $evaluator_note;
	private $is_available;

	public function no_after_save()
	{
		if ($this->is_available) {
			
			$sending = Sending::find_by_id($this->sending_id);

			if ( ! $sending)
				return false;

			$mail = new Mail(array(
				'to' => array(
					'name' => $sending->valued->name,
					'email' => $sending->valued->email
				),
				'subject' => 'PDF Avaliação',
				'message' => 'Olá #usuario! Suas respostas já foram corrigidas. Clique no link para visualizar o arquivo a ser impresso #questionario',
				'replacements' => array(
					'#questionario' => Sending::getUrlReply($sending->token),
					'#usuario' => $sending->valued->name
				)
			));

			try {

				$mail->send();

			} catch (Exception $e) {
				return $e->getMessage();
			}

		}
	}

	public function getSalary()
	{
		return number_format($this->salary, 2, ',', '.');
	}

	public function getNewSalary()
	{
		return number_format($this->new_salary, 2, ',', '.');
	}

	public function getEvaluatorNote()
	{
		return number_format($this->evaluator_note, 2, ',', '.');
	}

	public function getBonus()
	{
		return number_format($this->bonus, 2, ',', '.');
	}

	public function getPerf()
	{
		return number_format($this->perf, 2, ',', '.');
	}

	public function getNrSalary()
	{
		return number_format($this->nr_salary, 2, ',', '.');
	}

	public function getNrSalaryProp()
	{
		return number_format($this->nr_salary_prop, 2, ',', '.');
	}

	public function getFinalNote()
	{
		return number_format($this->final_note, 2, ',', '.');
	}
}