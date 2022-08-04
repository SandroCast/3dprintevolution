<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title')</title>

        <link rel="shortcut icon" href="/img/logo.jpg" type="image/x-icon" />

        <!-- Fa fa-tachometer -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

        <!-- Fonte do Google -->
        <link href="https://fonts.googleapis.com/css2?family=Roboto" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@200&display=swap" rel="stylesheet">

        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

        <!-- CSS Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">

        <!-- CSS da aplicação -->
        <link rel="stylesheet" href="/css/style_layouts.css">
        <script src="/js/script.js"></script>

        <style>
            #imgVerificaEmail{
                margin:0;
                padding:0;
                width: 100%;
                height: 100%;
                display: flex;
                position: fixed;
                opacity : 0.9;
                z-index: 2;
            }
            #formVerificaEmail{
                width: 300px;
                top:50%;
                left:50%;
                margin-left: -140px;
                margin-top: -180px;
                display: flex;
                z-index: 3;
                text-align: center;
                padding: 5px;
            }
            #time{
                color: red;
            }

        </style>
    </head>

    <body onload="srl(@if(session('click'))
    {{session('click')}}
    @endif)">

        {{--  verificação do email  --}}
        @auth
            @if($user->email_verificado == null)
                <style>
                    html, body {
                        overflow: hidden;
                    }
                    #main-header{
                        margin-top: -180px;
                    }
                    #conteudo{
                        margin-top: -180px;
                    }

                </style>
                <img id="imgVerificaEmail" src="/img/fundopreto.png" alt="">
                <div id="formVerificaEmail" class="card col-md-5">
                    <p>Enviamos um código em seu email. <br> Digite-o abaixo para continuar seu cadastro.</p>
                    <form action="/" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="title"><b>CÓDIGO</b></label><br>
                            <input id="inp1" onkeydown="digitar1()" autofocus style="width: 40px; text-align: center; display: inline;" type="text" class="form-control" name="title" maxlength="1" required>
                            <input id="inp2" onkeydown="digitar2()" style="width: 40px; text-align: center; display: inline;" type="text" class="form-control" name="title" maxlength="1" required>
                            <input id="inp3" onkeydown="digitar3()" style="width: 40px; text-align: center; display: inline;" type="text" class="form-control" name="title" maxlength="1" required>
                            <input id="inp4" onkeydown="digitar4()" style="width: 40px; text-align: center; display: inline;" type="text" class="form-control" name="title" maxlength="1" required>
                        </div>
                        <br>
                        <p>Após a terceira verificação incorreta ou <br> fim do tempo de virificação, você será redirecionado para a home do site.</p>
                        
                        <p id="time"></p>

                        <div class="form-group mt-4">
                            <a class="btn btn-danger pull-left" href="#">Cancelar</a>
                            <input type="submit" class="btn btn-primary pull-right" value="Verificar">
                        </div>

                    </form>
                    
                </div>
               
                @php
                    $tempo = strtotime('now') - strtotime(Auth::user()->created_at);
                @endphp

                <span class="bg-red">
                
                <script type="text/javascript">

                    function digitar1() {
                        var i1 = document.getElementById("inp1");
                        var i2 = document.getElementById("inp2");
                        if (i2.value.length == 0) {
                            setTimeout( () => {
                                if(i1.value.length == 0){
                                    document.getElementById("inp1").focus();
                                }else{
                                    document.getElementById("inp2").focus();
                                }
                            }, 0)
                        }
                    }
                    function digitar2() {
                        var i2 = document.getElementById("inp2");
                        var i3 = document.getElementById("inp3");
                        if (i3.value.length == 0) {
                            setTimeout( () => {
                                if(i2.value.length == 0){
                                    document.getElementById("inp2").focus();
                                }else{
                                    document.getElementById("inp3").focus();
                                }
                            }, 0)
                        }
                    }
                    function digitar3() {
                        var i3 = document.getElementById("inp3");
                        var i4 = document.getElementById("inp4");
                        if (i4.value.length == 0) {
                            setTimeout( () => {
                                if(i3.value.length == 0){
                                    document.getElementById("inp3").focus();
                                }else{
                                    document.getElementById("inp4").focus();
                                }
                            }, 0)
                        }
                    }

                    let tempo;
                    tempo = @json($tempo)

                    function ajax(){
                        var req = new XMLHttpRequest();
                        req.onreadystatechange = function(){
                            if (req.readyState == 4 && req.status == 200) {
                                document.getElementById('time').innerHTML = req.responseText;
                            }
                        }
                        tempo = tempo + 1;
                        req.open('GET', '/time/'+tempo, true);
                        req.send();
                    }
                    setInterval(function(){ajax();}, 1000);

                </script>
            @endif
        @endauth
        {{--  verificação do email  --}}

        <header id="main-header">

            <!-- Cabeçalho -->
            <nav id="navbartop">

                <nav id="partesuperior">
                    <a href="/"><strong>3D</strong>PrintEvolution</a>
                    @auth
                        <a onclick="event.preventDefault(); conf_mini()" href="#" ><img class="imgperfil" style="float: right; font-size: 15px; border-radius: 50%; margin: 5px 10px;" @if(Auth::user()->profile_photo_path != null) src="/storage/{{ Auth::user()->profile_photo_path }}" @else src="{{ $user->profile_photo_url }}" @endif alt="Perfil"></a>
                        <div class="card col-md-12" id="config">

                            <img class="imgconfig" style="border-radius: 50%; margin: 5px 35px;" @if(Auth::user()->profile_photo_path != null) src="/storage/{{ Auth::user()->profile_photo_path }}" @else src="{{ $user->profile_photo_url }}" @endif alt="Perfil">
                            <ul>
                                <li><a href="/user/profile">Meu Perfil</a></li>
                                <li><a href="/favorito">Favoritos</a></li>
                                <li><a href="#">Meus Pedidos</a></li>
                                @if($user->adms > 0)
                                <li><a href="/produtos">Produtos</a></li>
                                @endif
                                @if($user->adms > 1)
                                <li><a href="/usuarios">Usuários</a></li>
                                @endif
                                <li>
                                    <form action="/logout" method="POST">
                                        @csrf
                                        <a href="/logout" onclick="event.preventDefault(); this.closest('form').submit();">Sair</a>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @endauth
                    @guest
                        <a style="float: right; font-size: 15px;" href="/login">Entrar</a>
                    @endguest
                </nav>
                <nav id="menu">
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="#">Sobre</a></li>
                        <li><a href="#">Quem Somos</a></li>
                        <li><a href="#">Contato</a></li>
                        
                    </ul>
                </nav>

            </nav>


        </header>

        <main id="conteudo">
            @yield('content')
        </main>

        <footer id="footer">

        </footer>


        <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    </body>
</html>