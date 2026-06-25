<?php

require_once(__DIR__ . "/../../db.php");
require_once(__DIR__ . "/mail_config.php");

function enviarCorreoConfirmacion($inscrito_id, $conn)
{
    try {

        $stmt = $conn->prepare("
            SELECT i.nombre, i.correo, i.codigo, e.nombre AS evento
            FROM inscritos i
            LEFT JOIN eventos e ON e.id = i.evento_id
            WHERE i.id = ?
        ");

        if(!$stmt){
            return ["success" => false, "message" => "Error SQL prepare"];
        }

        $stmt->bind_param("i", $inscrito_id);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if(!$user || empty($user['correo'])){
            return ["success" => false, "message" => "Sin correo"];
        }

        $data = [
            "personalizations" => [[
                "to" => [[
                    "email" => $user['correo'],
                    "name" => $user['nombre']
                ]],
                "subject" => "Confirmación de inscripción - " . $user['evento']
            ]],
            "from" => [
                "email" => FROM_EMAIL,
                "name" => FROM_NAME
            ],
            "content" => [[
                "type" => "text/html",
                "value" => "
                    <h2>Hola {$user['nombre']}</h2>
                    <p>Tu pago fue confirmado correctamente.</p>
                    <p><b>Código:</b> {$user['codigo']}</p>
                    <p><b>Evento:</b> {$user['evento']}</p>
                "
            ]]
        ];

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.sendgrid.com/v3/mail/send",
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . SENDGRID_API_KEY,
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);

        if(curl_errno($ch)){
            return [
                "success" => false,
                "message" => "cURL error: " . curl_error($ch)
            ];
        }

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($httpcode == 202){
            return ["success" => true, "message" => "Correo enviado"];
        }

        return [
            "success" => false,
            "message" => "Error SendGrid HTTP $httpcode: " . $response
        ];

    } catch(Exception $e){
        return ["success" => false, "message" => $e->getMessage()];
    }
}