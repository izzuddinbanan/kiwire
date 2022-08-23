<?php

global $kiw_request, $kiw_api, $kiw_roles;


$kiw_required = array(
    "username",
    "password",
    "email_address",
    "tenant_id",
    "tenant_name",
    "hostname"
);


$kiw_module_ids = array(
    "Account -> Profile",
    "Account -> Auto Reset",
    "Account -> Account -> List",
    "General -> Change Theme",
    "General -> Reset 2-Factors",
    "General -> Register 2-Factors",
    "Report -> Bandwidth vs Users",
    "Report -> Bandwidth Usage Summary",
    "Report -> Bandwidth Usage User",
    "Report -> Top Historic Bandwidth User",
    "Report -> Top Current Bandwidth User",
    "Report -> Insight -> User Device Info",
    "Report -> Login Concurrent",
    "Report -> User Dwell Time",
    "Report -> Return Account",
    "Report -> Insight -> Social Network Data",
    "Report -> Insight -> Social Network Analytics",
    "Report -> Insight -> Sign-Up Data",
    "General -> Dashboard",
    "General -> Password",
    "Policy -> General",
    "Integration -> Social",
    "Login Engine -> Desiger Tool -> List",
    "Login Engine -> Journey",
    "Login Engine -> Media",
    "Login Engine -> Notification",
    "Login Engine -> Sign up -> One Click",
    "Login Engine -> Sign up -> Public",
    "Login Engine -> Template Engine",
    "Help -> User Account Diagnostic",
    "Help -> Find Mac Address"
);


if (in_array("Cloud -> Manage Client", $kiw_roles) == false) {

    die(json_encode(array("status" => "error", "message" => "This API key not allowed to access this module [ {$request_module[0]} ]", "data" => "")));

}


if ($kiw_request['method'] == "GET") {


    if (count($request_module) == 2) {


        $kiw_request['id'] = $kiw_db->escape($request_module[1]);

        $kiw_data = $kiw_db->query_first("SELECT * FROM kiwire_clouds WHERE tenant_id = '{$kiw_request['id']}' LIMIT 1");
  
  
    } else {


        if ($kiw_api['tenant_id'] == "superuser") {


            if (count($request_module) > 2) {

                $kiw_config['offset'] = (int)$request_module[1];
                $kiw_config['limit'] = (int)$request_module[2];
                $kiw_config['column'] = $kiw_db->escape($request_module[3]);
                $kiw_config['order'] = strtolower($request_module[4]) == "asc" ? "ASC" : "DESC";

            } else {

                $kiw_config['limit'] = 10;
                $kiw_config['offset'] = 0;
                $kiw_config['column'] = "id";
                $kiw_config['order'] = "DESC";

            }


            $kiw_data = $kiw_db->fetch_array("SELECT * FROM kiwire_clouds ORDER BY {$kiw_config['column']} {$kiw_config['order']} LIMIT {$kiw_config['offset']}, {$kiw_config['limit']}");


        } else {

            die(json_encode(array("status" => "error", "message" => "Only superuser can list all tenant detail", "data" => null)));

        }


    }


    echo json_encode(array("status" => "success", "message" => "", "data" => $kiw_data));


} elseif ($kiw_request['method'] == "POST") {


    require_once dirname(__FILE__, 2) . "/admin/includes/include_general.php";


    if ($kiw_api['tenant_id'] == "superuser") {


        // check for required info to execute

        $_REQUEST = file_get_contents("php://input");

        $_REQUEST = json_decode($_REQUEST, true);


        foreach ($kiw_required as $kiw_key) {


            if (empty($_REQUEST[$kiw_key])) {


                die(json_encode(array("status" => "error", "message" => "Missing required data [ {$kiw_key} ]", "data" => "")));


            } else $kiw_data[$kiw_key] = $kiw_db->escape($_REQUEST[$kiw_key]);


        }


        // sanitize tenant-id

        $kiw_data['tenant_id'] = preg_replace("/[^A-Za-z0-9_-]/", "", $kiw_data['tenant_id']);


        // check if the name already been used

        $kiw_test = $kiw_db->query_first("SELECT COUNT(*) AS kcount FROM kiwire_clouds WHERE tenant_id = '{$kiw_data['tenant_id']}'");


        if ($kiw_test['kcount'] == 0) {


            // if no tenant with the same id, then create one

            // create admin

            $kiw_temp['username']   = $kiw_db->escape($kiw_data['username']);
            $kiw_temp['password']   = sync_encrypt($kiw_data['password']);
            $kiw_temp['email']      = $kiw_db->escape($kiw_data['email_address']);
            $kiw_temp['fullname']   = "Administrator";
            $kiw_temp['monitor']    = "y";

            $kiw_temp['temp_pass']  = "y";
            $kiw_temp['groupname']  = "basic_admin";
            $kiw_temp['permission'] = "rw";
            $kiw_temp['tenant_id']  = $kiw_data['tenant_id'];

            $kiw_temp['theme']  = "dark";

            $kiw_db->insert("kiwire_admin", $kiw_temp);

            unset($kiw_temp);


            // create admin group access

            foreach ($kiw_module_ids as $kiw_module_id) {

                $kiw_db->query("INSERT INTO kiwire_admin_group(id, groupname, moduleid, tenant_id, updated_date) VALUE (NULL, 'basic_admin', '{$kiw_module_id}', '{$kiw_data['tenant_id']}', NOW())");

            }


            // create clouds

            $kiw_temp['tenant_id']  = $kiw_data['tenant_id'];
            $kiw_temp['name']       = $kiw_data['tenant_name'];
            $kiw_temp['ip_address'] = $kiw_data['hostname'];

            $kiw_temp['voucher_prefix']                 = strtoupper(substr(md5(time()), 6, 3) . "_");
            $kiw_temp['voucher_limit']                  = 5;
            $kiw_temp['campaign_wait_second']           = 15;
            $kiw_temp['campaign_multi_ads']             = "y";
            $kiw_temp['campaign_require_verification']  = "y";

            $kiw_temp['currency'] = "EUR";
            $kiw_temp['timezone'] = "Europe/Madrid";

            $kiw_temp['check_arrangement_login'] = "check_active,check_password,check_allow_simultaneous,check_allow_quota,check_allow_credit,check_zone_limit,check_allow_mac,check_allow_zone,check_register_mac,activate_voucher_account,reporting_process";
            $kiw_temp['check_arrangement_auto'] = "mac_auto_login";

            $kiw_db->insert("kiwire_clouds", $kiw_temp);

            unset($kiw_temp);


            // create custom directory

            if (file_exists(dirname(__FILE__, 2) . "/custom/{$kiw_data['tenant_id']}/") == false) {

                mkdir(dirname(__FILE__, 2) . "/custom/{$kiw_data['tenant_id']}/", 0755, true);

                 //create custom stylesheet directory
                 mkdir(dirname(__FILE__, 2) . "/custom/{$kiw_data['tenant_id']}/stylesheets", 0755, true);

                 //create custom login images directory
                 mkdir(dirname(__FILE__, 2) . "/custom/{$kiw_data['tenant_id']}/stylesheets/images", 0755, true);
 

            }


            // insert license key if available

            if (strlen($kiw_data['license_key']) > 0) {

                file_put_contents(dirname(__FILE__, 2) . "/custom/{$kiw_data['tenant_id']}/tenant.license", $kiw_data['license_key']);

            }


            if (file_exists(dirname(__FILE__, 3) . "/logs/{$kiw_data['tenant_id']}/") == false) {

                mkdir(dirname(__FILE__, 3) . "/logs/{$kiw_data['tenant_id']}/", 0755, true);

            }


            // create profile

            $kiw_temp['tenant_id']  = $kiw_data['tenant_id'];
            $kiw_temp['name']       = "Temp_Access";
            $kiw_temp['price']      = 0;
            $kiw_temp['type']       = "countdown";

            $kiw_attribute["control:Max-All-Session"]        = 600;
            $kiw_attribute["control:Simultaneous-Use"]       = 1;
            $kiw_attribute["control:Kiwire-Total-Quota"]     = 100;
            $kiw_attribute["reply:Acct-Interim-Interval"]    = 300;
            $kiw_attribute["reply:Idle-Timeout"]             = 300;
            $kiw_attribute["reply:WISPr-Bandwidth-Max-Down"] = 1024 * 1024;
            $kiw_attribute["reply:WISPr-Bandwidth-Max-Up"]   = 1024 * 1024;
            $kiw_attribute["reply:WISPr-Bandwidth-Min-Up"]   = 512 * 1024;
            $kiw_attribute["reply:WISPr-Bandwidth-Min-Down"] = 512 * 1024;

            $kiw_temp['attribute'] = json_encode($kiw_attribute);

            unset($kiw_attribute);


            $kiw_db->insert("kiwire_profiles", $kiw_temp);

            unset($kiw_temp);


            // create a default profile

            $kiw_temp['tenant_id']  = $kiw_data['tenant_id'];
            $kiw_temp['name']       = "Free_wifi";
            $kiw_temp['price']      = 0;
            $kiw_temp['type']       = "free";

            $kiw_attribute["control:Simultaneous-Use"]       = 2;
            $kiw_attribute["reply:Acct-Interim-Interval"]    = 1800;
            $kiw_attribute["reply:Idle-Timeout"]             = 300;
            $kiw_attribute["reply:WISPr-Bandwidth-Max-Down"] = 10240 * 1024;
            $kiw_attribute["reply:WISPr-Bandwidth-Max-Up"]   = 2048 * 1024;
            $kiw_attribute["reply:WISPr-Bandwidth-Min-Up"]   = 1024 * 1024;
            $kiw_attribute["reply:WISPr-Bandwidth-Min-Down"] = 1024 * 1024;

            $kiw_temp['attribute'] = json_encode($kiw_attribute);

            unset($kiw_attribute);


            $kiw_db->insert("kiwire_profiles", $kiw_temp);

            unset($kiw_temp);


            $kiw_random[] = substr(md5(time() . rand(0, 9999) . $kiw_data['tenant_id']), 0, 8);
            $kiw_random[] = substr(md5(time() . rand(0, 9999) . $kiw_data['tenant_id']), 0, 8);

            if ($kiw_random[0] == $kiw_random[1]){

                $kiw_random[1] = substr(md5(time() . rand(0, 9999) . rand(0, 1000) . $kiw_data['tenant_id']), 0, 8);

            }


            // $kiw_pages_tnc = <<<EOTC

            //                 <div class="row">
            //                     <div class="col-md-6 offset-md-3 col-sm-12">
            //                         <div class="card">
            //                             <div class="card-content">
            //                                 <div class="card-body">
            //                                     <div class="col-12 card-title">
            //                                         <h5 class="mb-2 center"><b>Términos y condiciones de uso</b></h5>
            //                                         <p style="text-align: center;"><b style="font-size: 14px !important;">Lea los siguientes términos y condiciones sobre el uso de este servicio de wifi gratuito&nbsp;</b></p>
            //                                     </div>
            //                                     <div class="row text-center">
            //                                         <div class="col-sm-12 text-center"><a href="javascript:void(0);" class="btn btn-primary text-center next-page-btn waves-effect waves-light">Siguiente</a></div>
            //                                     </div>
            //                                     <div class="col-12">
            //                                         <p><span class="font-weight-bold">Responsable del fichero</span></p>
            //                                         <p>
            //                                             ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“, en adelante ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“, con número CIF “”INTRODUZCA SU CIF””, domicilio social en “”INTRODUZCA SU DIRECCION FISCAL”” y dirección de
            //                                             email ““INTRODUZCA SU MAIL DE CONTACTO”“, es el responsable de los ficheros en los que se incluirán los datos personales recogidos y tratados desde esta página web. Estos ficheros se encuentran debidamente
            //                                             inscritos en el Registro General de Protección de Datos de la Agencia Española de Protección de Datos.
            //                                         </p>

            //                                         <p><span class="font-weight-bold">Recogida de datos</span></p>
            //                                         <p>
            //                                             ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ únicamente recoge los datos de carácter personal y de navegación que nos proporcione a través de los formularios incluidos en la página web, nuestras direcciones de
            //                                             correo electrónico y registros de trazabilidad especificados por la ley para, en caso de necesidad, colaboración con los cuerpos de seguridad del estado.
            //                                         </p>

            //                                         <p><span class="font-weight-bold">Uso y finalidades de los datos recogidos</span></p>
            //                                         <p>
            //                                             En ““INTRODUZCA SU DIRECCION WEB”“ se recogen los datos de carácter personal con las finalidades que se detallan a continuación, si no está conforme con alguna de ellas puede comunicarlo a la dirección de email
            //                                             ““INTRODUZCA SU MAIL DE CONTACTO”“. Los usos y finalidades previstos son:
            //                                         </p>

            //                                         <p><span class="font-weight-bold">Registro y gestión de usuarios</span></p>
            //                                         <p>
            //                                             La información que nos proporcione en el formulario de registro, se utilizará para la gestión de los usuarios de la página web. ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ no admitirá intentos de registros falsos
            //                                             o que suplanten la identidad de personas o empresas. El suministro de información falsa en el formulario de registro implicará la baja automática del usuario.
            //                                         </p>
            //                                         <p>Gestión de pedidos</p>
            //                                         <p>Cuando adquiere un producto o servicio en nuestra tienda online, los datos que nos facilite serán utilizados para tramitar y facturar los pedidos realizados.</p>
            //                                         <p>Publicación de comentarios</p>
            //                                         <p>La información que nos proporcione a través de los formularios, se utilizará para la gestión de los usuarios y publicación de comentarios en nuestro blog.</p>

            //                                         <p><span class="font-weight-bold">Comunicación de datos a terceros</span></p>
            //                                         <p>““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ tiene prevista la comunicación de sus datos personales a terceros en los supuestos que se detallan a continuación:</p>
            //                                         <p>
            //                                             -Comunicaciones establecidas en la Ley. Sus datos serán comunicados a terceros en los supuestos en los que una norma legal así lo establezca (administraciones públicas, autoridades administrativas o judiciales,
            //                                             etc).
            //                                         </p>
            //                                         <p>
            //                                             -Sus datos también serán comunicados a terceros cuando estos formen parte del círculo de partners/socios de ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ los cuales podrán, en algunos casos, utilizarlos para
            //                                             comunicaciones comerciales.
            //                                         </p>
            //                                         <p>Si no está conforme con alguna de estas cesiones puede comunicarlo a la dirección de email ““INTRODUZCA SU MAIL DE CONTACTO”“.</p>
            //                                         <p>Gestión de pagos</p>
            //                                         <p>
            //                                             Sus datos personales serán comunicados a terceros cuando resulte necesario para el pago de los productos o servicios adquiridos (entidades bancarias y/o financieras propietarias de los medios de pago utilizados).
            //                                         </p>

            //                                         <p><span class="font-weight-bold">Seguridad</span></p>
            //                                         <p>
            //                                             ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ utiliza tecnologías adecuadas al estado de la técnica actual, para proteger sus datos e informaciones personales, así nuestra página web se almacena en servidores
            //                                             seguros protegidos contra los tipos de ataques más habituales. No obstante, debemos recordarle que no existe tecnología invulnerable y que por tanto debe poner los medios que estén a su alcance para mantener el
            //                                             nivel de seguridad de sus datos, especialmente le recomendamos que utilice contraseñas robustas para el acceso a su cuenta de usuario, con al menos 8 caracteres que alternen cifras, letras, mayúsculas, minúsculas
            //                                             y caracteres especiales. Le recomendamos además que modifique su clave con periodicidad (al menos una vez al año) y que ante cualquier sospecha de que un tercero pueda conocerla, proceda a su inmediata
            //                                             modificación.
            //                                         </p>

            //                                         <p><span class="font-weight-bold">Confidencialidad</span></p>
            //                                         <p>
            //                                             ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ se obliga, de acuerdo establece el artículo 10 LOPD, a guardar secreto respecto a los datos personales accedidos, aún después de finalizada la relación comercial entre
            //                                             ambas partes, debiendo extender esta obligación a todo el personal de su organización que acceda a dichos ficheros. Este deber de secreto queda igualmente extendido al resto de información que no sean datos de
            //                                             carácter personal y a la cual tenga acceso o conocimiento durante la prestación de sus servicios. No obstante, el cliente autoriza a ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ a los solos efectos de su
            //                                             utilización como referencia comercial, a la divulgación de sus datos identificativos y/o signos distintivos, así como la condición de cliente de los productos y servicios ofrecidos por ““INTRODUZCA AQUI EL NOMBRE
            //                                             DE SU EMPRESA”“. Esta autorización se entiende concedida únicamente para la inclusión del CLIENTE en las campañas publicitarias, listas y portfolios de referencias comerciales que bajo cualquier medio edite
            //                                             ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“.
            //                                         </p>

            //                                         <p><span class="font-weight-bold">Medidas de seguridad aplicables al tratamiento de datos personales</span></p>
            //                                         <p>
            //                                             I. Nivel de Seguridad: Conforme a las indicaciones facilitadas por el Cliente sobre el nivel de seguridad aplicable a los ficheros con datos de carácter personal objeto de esta contratación, ““INTRODUZCA AQUI EL
            //                                             NOMBRE DE SU EMPRESA”“ manifiesta que aplicará las medidas del nivel de seguridad básico, descritas en el R.D. 1720/2007.
            //                                         </p>
            //                                         <p>
            //                                             II. Ámbito de Aplicación de las Medidas Técnicas y Organizativas de Seguridad: ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ manifiesta que aplicará las medidas técnicas y organizativas señaladas en este apartado a
            //                                             las funciones descritas en estas condiciones de contratación.
            //                                         </p>
            //                                         <p>III. Documento de Seguridad: ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ manifiesta que posee un Documento de Seguridad, conforme a lo establecido en el art. 88 del R.D. 1720/2008.</p>
            //                                         <p>
            //                                             IV. Manual de Funciones y Obligaciones: ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ declara que posee y mantiene actualizado un Manual de Funciones y Obligaciones del Personal respecto a sus Sistemas de
            //                                             Información y Ficheros con Datos Personales tal y como exige el art. 89 del R.D. 1720/2007, habiendo sido divulgado este Manual a todo su personal. ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ se compromete a no
            //                                             permitir el acceso ni tratamiento de ficheros con datos de carácter personal al personal que no haya recibido copia de dicho documento.
            //                                         </p>
            //                                         <p>
            //                                             V. Registro de Incidencias: ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ manifiesta que posee un Registro de Incidencias interno que cumple con los requisitos solicitados por el art. 90 del R.D. 1720/2007, siendo
            //                                             utilizado este Registro por su personal para el reporte de cualquier incidencia relacionada con la seguridad de la información y datos personales incluidos en los ficheros con datos personales que tratan.
            //                                         </p>
            //                                         <p>
            //                                             VI. Control de Acceso: ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ se compromete a implantar con carácter previo al inicio de sus servicios, cuantas medidas técnicas y organizativas sean necesarias para
            //                                             garantizar los principios de seguridad y confidencialidad de la información a la que tenga acceso como consecuencia de sus funciones y en particular manifiesta que cumple con las siguientes medidas respecto al
            //                                             control de acceso:
            //                                         </p>
            //                                         <p>-Mantiene una relación actualizada de usuarios y accesos autorizados.</p>
            //                                         <p>-Permite el acceso únicamente a los usuarios autorizados según las funciones asignadas a cada uno de ellos.</p>
            //                                         <p>-Establece mecanismos que evitan el acceso a datos o recursos con derechos distintos de los autorizados.</p>
            //                                         <p>-Los permisos de acceso los concede únicamente el personal autorizado para ello.</p>
            //                                         <p>
            //                                             VII. Identificación y Autenticación: ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ en su acceso a datos de carácter personal del Responsable del fichero mantiene las siguientes medidas de seguridad respecto a la
            //                                             identificación y autenticación de usuarios que van a tener acceso a dichos datos:
            //                                         </p>
            //                                         <p>-La identificación y autenticación es personalizada.</p>
            //                                         <p>-Existe un procedimiento de asignación y distribución de contraseñas, que impone el uso de contraseñas robustas.</p>
            //                                         <p>-Las contraseñas se almacenan de forma ininteligible.</p>
            //                                         <p>-Las contraseñas son confidenciales (únicamente conocidas por el usuario).</p>
            //                                         <p>-Las contraseñas se cambian al menos una vez al año.</p>
            //                                         <p>VIII. Gestión de Soportes: ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ deberá adoptar las siguientes medidas de seguridad respecto a los soportes con datos de carácter personal:</p>
            //                                         <p>-Mantener un inventario de soportes.</p>
            //                                         <p>-Establecer un sistema de etiquetado acorde con el sistema de inventario que permita además, identificar el tipo de información que contienen.</p>
            //                                         <p>-Almacenar en un lugar de acceso restringido los soportes autorizados.</p>
            //                                         <p>-Establecer un régimen de autorización de las salidas de soportes de sus instalaciones, incluidas las salidas a través de e-mail.</p>
            //                                         <p>-Adoptar medidas específicas destinadas a garantizar la confidencialidad y seguridad de los datos personales durante el transporte y desecho de soportes.</p>
            //                                         <p>
            //                                             IX. Copias de Seguridad: ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ manifiesta que posee un sistema de copias de seguridad que garantiza la recuperación de su información en el supuesto de ser necesario,
            //                                             probándose dicho sistema al menos una vez cada 6 meses.
            //                                         </p>
            //                                         <p>
            //                                             X. Ficheros no Automatizados: Respecto a los documentos con datos de carácter personal a los que ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ tenga acceso durante el desempeño de su trabajo, éste adoptará las
            //                                             siguientes medidas:
            //                                         </p>
            //                                         <p>-Mantendrá la documentación en archivadores, cajones o armarios que tengan dispositivos que obstaculicen su apertura.</p>
            //                                         <p>-Durante la revisión o tramitación de los documentos, la persona a cargo de los mismos debe ser diligente y custodiarla para evitar accesos no autorizados.</p>
            //                                         <p>-Sólo tendrán acceso a los documentos el personal que se encuentre autorizado.</p>
            //                                         <p>
            //                                             -Si se produce un traslado de documentación deberán adoptarse medidas de seguridad que impidan la pérdida o acceso por terceros a dicha documentación (traslado en dispositivos cerrados y discretos, evitar perder
            //                                             de vista los documentos, etc).
            //                                         </p>
            //                                         <p>
            //                                             XI. Personal de ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“: ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“, se compromete a comunicar estas obligaciones expuestas a su personal, velando por el cumplimiento de esta
            //                                             normativa, entre los empleados incluidos en el ámbito objetivo de la presente contratación.
            //                                         </p>

            //                                         <p><span class="font-weight-bold">Subcontratación</span></p>
            //                                         <p>
            //                                             El CLIENTE autoriza expresamente a ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“ para la subcontratación del servicio de alojamiento de datos (hosting) utilizado para la gestión y provisión de nuestros servicios.
            //                                             Si desea conocer la identidad de los proveedores empleados por ““INTRODUZCA AQUI EL NOMBRE DE SU EMPRESA”“, puede solicitárnoslo a través de la dirección de email ““INTRODUZCA SU MAIL DE CONTACTO”“.
            //                                         </p>
            //                                         <p>
            //                                             Ejercicio de Derechos: En cualquier momento podrá ejercer los Derechos de acceso, rectificación, cancelación y oposición mediante el envío de la solicitud de ejercicio correspondiente junto con copia del
            //                                             documento oficial que le identifique (DNI, carnet de conducir o pasaporte) a la dirección de correo electrónico ““INTRODUZCA SU MAIL DE CONTACTO”“.
            //                                         </p>
            //                                     </div>
            //                                     <div class="row text-center">
            //                                         <div class="col-sm-12 text-center"><a href="javascript:void(0);" class="btn btn-primary text-center next-page-btn waves-effect waves-light">Siguiente</a></div>
            //                                     </div>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>
            //                 </div>



            //             EOTC;


            // $kiw_temp['id']             = "NULL";
            // $kiw_temp['tenant_id']      = $kiw_data['tenant_id'];
            // $kiw_temp['updated_date']   = "NOW()";
            // $kiw_temp['unique_id']      = $kiw_random[0];
            // $kiw_temp['page_name']      = "Terms_And_Condition";
            // $kiw_temp['count_impress']  = "y";
            // $kiw_temp['purpose']        = "landing";
            // $kiw_temp['content']        = base64_encode(urlencode($kiw_pages_tnc));
            // $kiw_temp['remark']         = "";
            // $kiw_temp['default_page']   = "y";
            // $kiw_temp['bg_lg']          = "";
            // $kiw_temp['bg_md']          = "";
            // $kiw_temp['bg_sm']          = "";
            // $kiw_temp['bg_css']         = "";

            // $kiw_db->insert("kiwire_login_pages", $kiw_temp);

            // unset($kiw_temp);

            // unset($kiw_pages_tnc);



            // $kiw_pages_main = <<<EOTC

            //                     <div class="display">
            //                         <h1 style="text-align: center;">
            //                             <img src="/custom/MASMOVIL/uploads/mm-logo-negocios.png" data-filename="mm-logo-negocios.png" alt="" />
            //                             <br />
            //                             <span style="color: rgb(53, 53, 53);">Bienvenidos <br /></span>
            //                         </h1>
            //                         <p style="text-align: center;"><span style="color: rgb(53, 53, 53);">Seleccione una de las siguientes opciones para su conexión a la red</span></p>
            //                     </div>

            //                     <div class="row mb-3">
            //                         <div class="col-md-6 offset-md-3 col-sm-12">
            //                             <div class="card">
            //                                 <div class="card-content">
            //                                     <div class="card-body">
            //                                         <form action="/user/one/?session={{session_id}}" method="post">
            //                                             <div class="notification text-center text-danger font-weight-bold m-50"></div>

            //                                             <div class="col-sm-12 center">
            //                                                 <p class="mb-2"><b>Debe aceptar&nbsp;</b><b style="letter-spacing: 0.01rem;">los términos&nbsp;y condiciones&nbsp;</b></p>
            //                                                 <p class="mb-2"><b style="letter-spacing: 0.01rem;">anteriormente mencionados para poder&nbsp; acceder</b></p>
            //                                             </div>

            //                                             <div class="custom-control custom-checkbox center">
            //                                                 <input type="checkbox" class="custom-control-input" id="tnc" name="tnc" required="" value="" />
            //                                                 <label for="tnc" class="custom-control-label">Acepto los terminos y condiciones</label>
            //                                             </div>

            //                                             <div class="col-sm-12 center mt-75">
            //                                                 <p style="text-align: center;">Por favor, rellene el siguiente formulario para poder acceder a la red</p>
            //                                                 <p style="text-align: center;"><input type="text" name="email_address" id="email_address" value="" data-parsley-required="true" placeholder="E-mail" /></p>
            //                                                 <button type="submit" class="btn btn-primary center waves-effect waves-light">Continuar</button>
            //                                             </div>
            //                                         </form>
            //                                     </div>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>

            //                     <div class="row mb-3">
            //                         <div class="col-md-6 offset-md-3 col-sm-12">
            //                             <div class="card">
            //                                 <div class="card-content">
            //                                     <div class="card-body">
            //                                         <div class="col-sm-12 card-title">
            //                                             <h5 class="mb-2 center"><b>O use una de sus redes sociales</b></h5>
            //                                             <h5 class="mb-2 center">
            //                                                 <a href="/user/social/?type=facebook&session={{session_id}}" class="mr-1" style="text-align: left; letter-spacing: 0.01rem;"><img src="/admin/designer/uploads/social/socialbox_facebook.png" /></a>
            //                                                 <a href="/user/social/?type=twitter&session={{session_id}}" class="mr-1" style="text-align: left; letter-spacing: 0.01rem;"><img src="/admin/designer/uploads/social/socialbox_twitter.png" /></a>
            //                                                 <a href="/user/social/?type=instagram&session={{session_id}}" class="mr-1" style="text-align: left; letter-spacing: 0.01rem;"><img src="/admin/designer/uploads/social/socialbox_instagram.png" /></a>
            //                                             </h5>
            //                                         </div>
            //                                     </div>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>

            //                 EOTC;


            // $kiw_temp['id']             = "NULL";
            // $kiw_temp['tenant_id']      = $kiw_data['tenant_id'];
            // $kiw_temp['updated_date']   = "NOW()";
            // $kiw_temp['unique_id']      = $kiw_random[1];
            // $kiw_temp['page_name']      = "Login";
            // $kiw_temp['count_impress']  = "y";
            // $kiw_temp['purpose']        = "landing";
            // $kiw_temp['content']        = base64_encode(urlencode($kiw_pages_main));
            // $kiw_temp['remark']         = "";
            // $kiw_temp['default_page']   = "y";
            // $kiw_temp['bg_lg']          = "";
            // $kiw_temp['bg_md']          = "";
            // $kiw_temp['bg_sm']          = "";
            // $kiw_temp['bg_css']         = "";

            // $kiw_db->insert("kiwire_login_pages", $kiw_temp);

            // unset($kiw_temp);

            // unset($kiw_pages_main);


            // copy the thumbnail for each pages


            if (file_exists("../custom/{$kiw_data['tenant_id']}/thumbnails/") == false){

                mkdir("../custom/{$kiw_data['tenant_id']}/thumbnails/", 0755, true);

            }


            if (file_exists("../custom/tandc.png")){

                copy("../custom/tandc.png", "../custom/{$kiw_data['tenant_id']}/thumbnails/{$kiw_random[0]}.png");

            }

            if (file_exists("../custom/login.png")){

                copy("../custom/login.png", "../custom/{$kiw_data['tenant_id']}/thumbnails/{$kiw_random[1]}.png");

            }


            // set the default social media setting

            $kiw_integration['tenant_id']       = $kiw_data['tenant_id'];
            $kiw_integration['profile']         = "Free_wifi";
            $kiw_integration['validity']        = "3600";
            $kiw_integration['allowed_zone']    = "none";
            $kiw_integration['facebook_en']     = "y";
            $kiw_integration['twitter_en']      = "y";
            $kiw_integration['instagram_en']    = "y";

            $kiw_db->insert("kiwire_int_social", $kiw_integration);

            unset($kiw_integration);


            // set default auto-reset

            /*
            $kiw_temp['id']             = "NULL";
            $kiw_temp['tenant_id']      = $kiw_data['tenant_id'];
            $kiw_temp['updated_date']   = "NOW()";
            $kiw_temp['exec_when']      = "ot";
            $kiw_temp['profile']        = "Free_wifi";
            $kiw_temp['grace']          = 0;

            $kiw_db->insert("kiwire_auto_reset", $kiw_temp);
            */

            unset($kiw_temp);


            $kiw_db->insert("kiwire_notification", array("tenant_id" => $kiw_data['tenant_id']));


            $kiw_notification = array();

            $kiw_notification['notification_account_created']   = "Your account has been created.";
            $kiw_notification['notification_password_reset']    = "Your password has been reset. Please check your Email Inbox / SMS.";
            $kiw_notification['error_no_credential']            = "Please provide credential to login.";
            $kiw_notification['error_wrong_otp']                = "You have provided wrong OTP code.";
            $kiw_notification['error_username_existed']         = "This username already existed in the system.";
            $kiw_notification['error_future_value_date']        = "Your account can only login after {{value_date}}";
            $kiw_notification['error_account_inactive']         = "This account is not active.";
            $kiw_notification['error_wrong_credential']         = "You have provided wrong username or password.";
            $kiw_notification['error_reached_quota_limit']      = "You have reached quota limit.";
            $kiw_notification['error_reached_time_limit']       = "You have reached time limit.";

            $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_data['tenant_id']}'");

            unset($kiw_notification);

            $kiw_notification = array();


            $kiw_notification['error_max_simultaneous_use']     = "You have reached max simultaneous use limit.";
            $kiw_notification['error_zone_restriction']         = "You are not allowed to login from this zone.";
            $kiw_notification['error_wrong_mac_address']        = "You are not allowed to login using this device.";
            $kiw_notification['error_zone_reached_limit']       = "This zone already reached maximum limit of login.";
            $kiw_notification['error_invalid_email_address']    = "You have provided invalid email address.";
            $kiw_notification['error_invalid_phone_number']     = "You have provided invalid phone number.";
            $kiw_notification['error_no_profile_subscribe']     = "This account has not subscribe to any profile.";
            $kiw_notification['error_wrong_captcha']            = "You have provided wrong captcha code.";
            $kiw_notification['error_country_code']             = "You are not allowed to register using this country code.";
            $kiw_notification['error_device_blacklisted']       = "This device has been blacklisted.";

            $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_data['tenant_id']}'");

            unset($kiw_notification);

            $kiw_notification = array();


            $kiw_notification['error_password_expired']         = "Your password already expired. Please change immediately.";
            $kiw_notification['error_password_contained_num']   = "Your password must contain atleast a number.";
            $kiw_notification['error_password_contained_alp']   = "Your password must contain atleast a character.";
            $kiw_notification['error_password_contained_sym']   = "Your password must contain atleast a symbol.";
            $kiw_notification['error_password_length']          = "Your password must be atleast {{character_count}} character long.";
            $kiw_notification['error_password_not_same']        = "You are not allowed to use same password as previous.";
            $kiw_notification['error_password_max_attemp']      = "You have reached max login attempts.";
            $kiw_notification['error_pass_username_matched']    = "You are not allowed to use username as your password.";
            $kiw_notification['error_password_reused']          = "You are not allowed to use previous password.";
            $kiw_notification['error_user_email_mismatched']    = "This email address are not belong to the account.";

            $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_data['tenant_id']}'");

            unset($kiw_notification);

            $kiw_notification = array();


            $kiw_notification['error_user_sms_mismatched']      = "This phone number not belong to the account.";
            $kiw_notification['error_user_not_found']           = "We unable to locate this account. Please try again.";
            $kiw_notification['error_username_cannot_space']    = "Username cannot have any space.";
            $kiw_notification['error_missing_sponsor_email']    = "Please provide your sponsor email address.";
            $kiw_notification['error_empty_password']           = "Please provide a valid password.";
            $kiw_notification['notification_password_changed']  = "Your password has been changed. Please login using new password.";
            $kiw_notification['error_inactive_account']         = "Your account already inactive.";
            $kiw_notification['error_ot_reset_grace']           = "You need to wait another {{remaining_minute}} minutes before you are allowed to login.";
            $kiw_notification['error_password_need_to_change']  = "You need to change your password upon the first login.";
            $kiw_notification['error_password_change_day']      = "You need to change your password every 90 days.";
            $kiw_notification['error_missing_credential_check']     = "Please provide your account ID.";
            $kiw_notification['error_password_verification_failed'] = "You have entered wrong password or verfication.";
            $kiw_notification['error_password_too_much_retries']    = "Too many retries. Your account has been suspended.";

            $kiw_db->update("kiwire_notification", $kiw_notification, "tenant_id = '{$kiw_data['tenant_id']}'");

            unset($kiw_notification);


            // set default journey

            // $kiw_temp['id']              = "NULL";
            // $kiw_temp['tenant_id']       = $kiw_data['tenant_id'];
            // $kiw_temp['updated_date']    = "NOW()";
            // $kiw_temp['journey_name']    = "Free_wifi";
            // $kiw_temp['page_list']       = implode(",", $kiw_random);
            // $kiw_temp['created_by']      = "API";
            // $kiw_temp['created_when']    = "NOW()";
            // $kiw_temp['status']          = "y";
            // $kiw_temp['lang']            = "en";
            // $kiw_temp['pre_login']       = "default";
            // $kiw_temp['pre_login_url']   = "";
            // $kiw_temp['post_login']      = "custom";
            // $kiw_temp['post_login_url']  = urlencode("https://www.masmovil.es");

            // $kiw_db->insert("kiwire_login_journey", $kiw_temp);

            // unset($kiw_temp);


            // set default zone

            // $kiw_temp['id']              = "NULL";
            // $kiw_temp['tenant_id']       = $kiw_data['tenant_id'];
            // $kiw_temp['updated_date']    = "NOW()";
            // $kiw_temp['name']            = "Free_wifi";
            // $kiw_temp['status']          = "y";
            // $kiw_temp['created_by']      = "Vola API";
            // $kiw_temp['auto_login']      = "";
            // $kiw_temp['simultaneous']    = "";
            // $kiw_temp['journey']         = "Free_wifi";
            // $kiw_temp['priority']        = "999";
            // $kiw_temp['force_profile']   = "";

            // $kiw_db->insert("kiwire_zone", $kiw_temp);

            // unset($kiw_temp);


            // $kiw_temp['id']              = "NULL";
            // $kiw_temp['tenant_id']       = $kiw_data['tenant_id'];
            // $kiw_temp['updated_date']    = "NOW()";
            // $kiw_temp['master_id']       = "Free_wifi";
            // $kiw_temp['nasid']           = "";
            // $kiw_temp['ipaddr']          = "0.0.0.0/0";
            // $kiw_temp['vlan']            = "";
            // $kiw_temp['ssid']            = "";
            // $kiw_temp['dzone']           = "";
            // $kiw_temp['priority']        = "999";
            // $kiw_temp['hash']            = "";

            // $kiw_db->insert("kiwire_zone_child", $kiw_temp);

            // unset($kiw_temp);


            // copy extra data json to custom

            $kiw_temp = @file_get_contents(dirname(__FILE__, 2) . "/user/templates/kiwire-data-mapping.json");

            $kiw_temp = json_decode($kiw_temp, true);


            for ($kiw_x = 0; $kiw_x < count($kiw_temp); $kiw_x++){

                if ($kiw_temp[$kiw_x]['field'] == "email_address"){

                    $kiw_temp[$kiw_x]['required'] = "Yes";

                }

            }

            @file_put_contents(dirname(__FILE__, 2) . "/custom/{$kiw_data['tenant_id']}/data-mapping.json", json_encode($kiw_temp));

            unset($kiw_temp);


            // set default sign-up setting

            $kiw_temp['id']               = "NULL";
            $kiw_temp['tenant_id']        = $kiw_data['tenant_id'];
            $kiw_temp['updated_date']     = "NOW()";
            $kiw_temp['enabled']          = "y";
            $kiw_temp['profile']          = "Free_wifi";
            $kiw_temp['validity']         = "365";
            $kiw_temp['after_register']   = "internet";
            $kiw_temp['allowed_zone']     = "none";
            $kiw_temp['data']             = "email_address";
            $kiw_temp['public_remark']    = "";

            $kiw_db->insert("kiwire_signup_public", $kiw_temp);

            unset($kiw_temp);


            // policies

            $kiw_temp['id']                     = "NULL";
            $kiw_temp['tenant_id']              = $kiw_data['tenant_id'];
            $kiw_temp['updated_date']           = "NOW()";
            $kiw_temp['mac_auto_login']         = "y";
            $kiw_temp['mac_auto_login_days']    = "365";

            $kiw_db->insert("kiwire_policies", $kiw_temp);

            unset($kiw_temp);


            $kiw_temp['id']               = "NULL";
            $kiw_temp['tenant_id']        = $kiw_data['tenant_id'];
            $kiw_temp['updated_date']     = "NOW()";
            $kiw_temp['enabled']          = "y";
            $kiw_temp['profile']          = "Free_wifi";
            $kiw_temp['validity']         = "365";
            $kiw_temp['login_using_id']   = "MAC";
            $kiw_temp['allowed_zone']     = "none";
            $kiw_temp['data']             = "email_address";

            $kiw_db->insert("kiwire_one_click_login", $kiw_temp);

            unset($kiw_temp);


            if ($_REQUEST['send_email'] == "y"){


                $kiw_email_content = @file_get_contents(dirname(__FILE__, 2) . "/user/templates/email-tenant.html");


                if (!empty($kiw_email_content)){


                    // get the first row as subject

                    $kiw_subject = explode(PHP_EOL, $kiw_email_content)[0];

                    $kiw_subject = trim($kiw_subject);


                    $kiw_email_content = preg_replace('/^.+\n/', '', $kiw_email_content);


                    $kiw_email = array();


                    $kiw_email['content'] = htmlentities(str_replace(array('{{username}}', '{{password}}', '{{tenant_id}}'), array($kiw_data['username'], stripcslashes($kiw_data['password']), $kiw_data['tenant_id']), $kiw_email_content));


                    $kiw_email['action']        = "send_email";
                    $kiw_email['tenant_id']     = "superuser";
                    $kiw_email['email_address'] = $kiw_db->escape($kiw_data['email_address']);
                    $kiw_email['subject']       = $kiw_subject;
                    $kiw_email['name']          = $kiw_db->escape($kiw_data['email_address']);


                    $kiw_connection = curl_init();


                    curl_setopt($kiw_connection, CURLOPT_URL, "http://127.0.0.1:9956");
                    curl_setopt($kiw_connection, CURLOPT_POST, true);
                    curl_setopt($kiw_connection, CURLOPT_POSTFIELDS, http_build_query($kiw_email));
                    curl_setopt($kiw_connection, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($kiw_connection, CURLOPT_TIMEOUT, 5);
                    curl_setopt($kiw_connection, CURLOPT_CONNECTTIMEOUT, 5);


                    curl_exec($kiw_connection);
                    curl_close($kiw_connection);


                }


            }


            echo json_encode(array("status" => "success", "message" => "Tenant ID [ {$kiw_data['tenant_id']} ] has been created", "data" => null));


        } else {

            echo json_encode(array("status" => "error", "message" => "Tenant ID already existed", "data" => ""));

        }


    } else {

        echo json_encode(array("status" => "error", "message" => "Only superuser can create new tenant", "data" => ""));

    }



} elseif ($kiw_request['method'] == "PATCH") {


    // check for required info to execute

    $_REQUEST = file_get_contents("php://input");

    $_REQUEST = json_decode($_REQUEST, true);


    foreach (array("tenant_id") as $kiw_key) {


        if (empty($_REQUEST[$kiw_key])) {

            die(json_encode(array("status" => "error", "message" => "Missing required data [ {$kiw_key} ]", "data" => "")));

        } else $kiw_data[$kiw_key] = $kiw_db->escape($_REQUEST[$kiw_key]);


    }


    // add remaining variable set

    foreach ($_REQUEST as $kiw_key => $kiw_value){

        if (!isset($kiw_data[$kiw_key]) && !in_array($kiw_key, array("id", "updated_date", "tenant_id"))){

            $kiw_data[$kiw_key] = $kiw_db->escape($kiw_value);

        }


    }


    if (count($request_module) == 2) {


        $kiw_request['id'] = $kiw_db->escape($request_module[1]);

        $kiw_db->update("kiwire_clouds", $kiw_data, "tenant_id = '{$kiw_data['tenant_id']}' LIMIT 1");


        if ($kiw_db->db_affected_row > 0) {

            echo json_encode(array("status" => "success", "message" => "", "data" => ""));

        } else echo json_encode(array("status" => "error", "message" => "", "data" => ""));


    } else die(json_encode(array("status" => "error", "message" => "Missing ID for this request", "data" => "")));


} elseif ($kiw_request['method'] == "DELETE") {


    if (count($request_module) == 2) {


        if ($kiw_api['tenant_id'] == "superuser") {


            // need to delete data from all tables except reports

            $kiw_request = $kiw_db->escape($request_module[1]);

            $kiw_request = $kiw_db->query_first("SELECT tenant_id FROM kiwire_clouds WHERE tenant_id = '{$kiw_request}' LIMIT 1 ");


            if (!empty($kiw_request['tenant_id'])) {


                $kiw_tables = $kiw_db->fetch_array("SELECT DISTINCT(TABLE_NAME) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'kiwire' AND COLUMN_NAME = 'tenant_id'");

                foreach ($kiw_tables as $kiw_table) {

                    if (strpos($kiw_table, "_report_") == false && strpos($kiw_table, "_sessions_") == false) {

                        $kiw_db->query("DELETE FROM {$kiw_table['TABLE_NAME']} WHERE tenant_id = '{$kiw_request['tenant_id']}'");

                    }


                }


                if (file_exists(dirname(__FILE__, 2) . "/custom/{$kiw_request['tenant_id']}/") == true) {

                    system("rm -rf " . dirname(__FILE__, 2) . "/custom/{$kiw_request['tenant_id']}/");

                }


                echo json_encode(array("status" => "success", "message" => "Tenant ID [ {$kiw_request['tenant_id']} ] has been deleted", "data" => ""));


            } else {

                echo json_encode(array("status" => "error", "message" => "Invalid tenant id", "data" => ""));

            }


        } else {

            echo json_encode(array("status" => "error", "message" => "Only superuser can delete tenant", "data" => ""));

        }

  
    } else {

        echo json_encode(array("status" => "error", "message" => "Missing tenant id", "data" => ""));

    }


}
