<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Paciente
 * 
 * @property int $idpaciente
 * @property int $rut
 * @property string $dv
 *
 * @package App\Models
 */
class Paciente extends Model
{
	protected $table = 'paciente';
	protected $primaryKey = 'idpaciente';
	public $timestamps = false;

	protected $casts = [
		'rut' => 'int'
	];

	protected $fillable = [
		'rut',
		'dv'
	];
}
