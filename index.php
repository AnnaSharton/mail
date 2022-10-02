<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма обратной связи</title>
    <style>
        body {padding: 25px;}
        span {color:red;}
        .email-sent {font-size: 20px; border: 1px solid #000; background-color: lightgreen; padding: 10px;}
        .error {border: 1px solid gray; background: #faa5a5; padding: 3px;}
        .question {width:50%; border: 1px solid gray; background: #d4c8e0; padding: 3px;}
    </style>
</head>
<body>
    <?php
    error_reporting(E_ALL);
    mb_internal_encoding("UTF-8");
    function findChar($string) { //функция для поиска недопустимых символов в имени пользователя
        $arr=array('.', ',', ';', '<', '>', '/', '|', '\'', ':', '!', '#', '@', '%', '^', '&', '*', '(', ')');
        foreach ($arr as $v) {
        if(strpos($string, $v)!== false) return true;
        }return false;
    }
    session_start();
    $questionArray=array( //массив для капчи, ответом будет ключ
        'час'=>'Делу время – потехе...',
        'смотрит'=>'Сколько волка не корми – он всё равно в лес...',
        'машут'=>'После драки кулаками не...',
        'разевай'=>'На чужой каравай – роток не...',
        'подает'=>' Кто рано встает, тому Бог...'
    );
    
    $showForm=1;
    $error=[];//создаю массив для ошибок
    //если данные заполнены то проверяю на ошибки 
    if (isset($_POST['email']) and isset($_POST['textarea']) and isset($_POST['email']) and isset($_POST['userName']) and isset($_POST['capcha'])) {
     
        
        $to='annasharton@yandex.ru';
        $userName=$_POST['userName'];
        $from=trim($_POST['email']); //обрезаю пробелы если есть
        $subject='Заявка с сайта www.домен.ру';
        $message=htmlspecialchars($_POST['textarea']);
        $message=urldecode($message);//предотвратьить отправку ссылок
        $message=trim($message);
        
        if (empty($userName)) { //не заполнено имя
            $error[]= 1; //заношу в массив какой-нибудь элемент
            $showForm=1;
        }
        if (mb_strlen($userName)===1 or findChar($userName)){ //проверяю минимальную длину имени (наверно имя может быть 2 и более знаков) и нет ли в ней недопустимых символов
            $error[]= 2;
            $showForm=1;
        } 
        if (empty($from)) {
            $error[]= 3;
            $showForm=1;
        }
        if ((mb_strlen($from)<6 and mb_strlen($from)>1) and !strpos($from, '@') and !strpos($from, '.')) {//в маиле должна быть @ и .
            $error[]= 4;
            $showForm=1;
        }
        if (empty($message)) {
            $error[]= 5;
            $showForm=1;
        }
        if (empty($_POST['capcha'])) { 
            $error[]= 6;
            $showForm=1;
        }
        if (!empty($_POST['capcha']) and strtolower(trim($_POST['capcha']))!=$_SESSION['res']) { //сравниваю ключ массива со значением ПОСТ
            $error[]= 7;
            $showForm=1;
        }
        else if (count($error)===0)  {
            if(mail($to, $subject, $message)){ //если письмо отправлено то вывожу сбщ:
                header('Location: index.php?sent=ok'); //редирект чтобы не было повторной отправки
            } 
        } else {}
        $_SESSION['data']=$userName;
       
    }
    $_SESSION['res']=array_rand($questionArray);//выбираю случайный ключ

    //если письмо отправилось, вывожу:
    if (isset($_GET['sent']) and $_GET['sent']==='ok')  { 
        $showForm=0;
        $a=$_SESSION['data'];
        echo '<div class="email-sent">'.$a.', ваше письмо отправлено<br>
             <a href=index.php>Отправить снова</a></div>';      
    }
    
    else if ($showForm=1) {
   
        if (count($error)>0) { 
            echo '<div class="error">Проверьте корректность заполнения формы</div>';}
        
        $name=(isset($_POST['userName'])) ? $_POST['userName'] : '';
        $mail=(isset($_POST['email'])) ? $_POST['email'] : '';
        $phone=(isset($_POST['phone'])) ? $_POST['phone'] : '';
        $textarea=(isset($_POST['textarea'])) ? $_POST['textarea'] : '';
        
        ?> 
        <form method="post" name="emailSend">
            <h3>Введите ваши данные (поля<span>*</span> обязательны для заполнения)</h3>
            <?php 
                    if(in_array(1, $error)) {
                        echo '<span>Вы не указали имя</span>';}
                    if(in_array(2, $error)) {
                        echo '<span>Укажите корректное имя</span>';}
            ?>
            <p>Введите ваше имя<span>*</span><br> <!--заношу в value значение из POST если оно уже задано-->
            <input type="text" name="userName" placeholder="Иван Иванов" value="<?=$name?>"></p>
            <?php   
                    if(in_array(3, $error)) {
                        echo '<span>Вы не указали email</span>';}
                    if (in_array(4, $error)) {
                        echo '<span>Укажите корректный email</span>';}
            ?>
            <p>Ваш email<span>*</span><br>
            <input type="email" name="email" placeholder="Введите ваш email" value="<?=$mail?>"></p>
            <p>Ваш телефон<br>
            <input type="text" name="phone" placeholder="+7900-000-00-00" value="<?=$phone?>">
            <?php   
                     if(in_array(5, $error)) {
                        echo '<p><span>Вы не написали сообщение</span></p>';}
            ?>
            <p>Введите ваше сообщение<span>*</span><br>
            <textarea name="textarea" cols="50" rows="10" value="<?=$textarea?>"><?php if (isset($_POST['textarea'])) echo $_POST['textarea'];?></textarea> 
            <p>Закончите пословицу:</p>
            <div class="question"><?=$questionArray[$_SESSION['res']]?></div><!--вывожу случайный вопрос из массива-->
            <?php   
                    if(in_array(6, $error)) {
                        echo '<p><span>Вы не ввели ответ</span></p>';}
                    if(in_array(7, $error)) {
                        echo '<p><span>Неверный ответ</span></p>';
                    }
            ?>
            <p><input type="text" name="capcha" placeholder="Введите ответ"></p> <!--при ошибках в заполнении формы это поле вроде обычно сбрасывается?-->
            <p><input type="submit" value="Отправить"></p>
        </form>
    <?php }
?>
</body>
</html>