<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\User;
use App\Models\FotoProduto;
use BaconQrCode\Renderer\Color\Rgb;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ProductController extends Controller
{

    public function index()
    {
        $user = auth()->user();

        $search = request('search');

        if($user){
            $favoritos = Favorite::where('user_id', $user->id)->get();
        }else{
            $favoritos = 0;
        }

        if($search) {

            $products = Product::where([
                ['title', 'like', '%'.$search.'%']

            ])->orderBy('created_at', 'DESC')->get();

        } else {
            $products = Product::orderBy('created_at', 'DESC')->skip(0)->take(30)->get();
        }

        return view('produtos.index', ['favoritos' => $favoritos, 'products' => $products, 'search' => $search, 'user' => $user]);  

    }

    public function favorito(Request $request)
    {
        $user = auth()->user();

        $produtos = Favorite::where('user_id', $user->id)->get();

        return view('produtos.favoritos', compact('user', 'produtos'));
    }

    public function favorito_remover($id)
    {
        Favorite::findOrFail($id)->delete();

        return redirect('/favorito')->with('msg', 'Produto removidodos favoritos com sucesso!');

    }

    public function create()
    {
        $user = auth()->user();

        if($user->adms > 0){
            return view('produtos.create', ['user' => $user]);
        }else{
            return redirect('/');
        }

    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $product = new Product;

        $product->title = $request->title;
        $product->description = $request->description;
        $product->preco = $request->preco;
        $product->user_id = $user->id;
        $product->save();

        //Image Upload
        if($request->image) {

            foreach($request['image'] as $imagem){

                $requestImage = $imagem;
                $extension = $requestImage->extension();
                $imageName = md5($requestImage->getClientOriginalName() . strtotime("now")) . "." . $extension;
                $requestImage->move(public_path('img/produtos'), $imageName);

                $fotoProduto = new FotoProduto;
                $fotoProduto->id_produto = $product->id;
                $fotoProduto->path = $imageName;
                $fotoProduto->save();

            }

        }

        return redirect('/product/edit/'.$product->id)->with('msg', 'Produto adicionado com sucesso!');

      //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $user = auth()->user();
        $product = Product::findOrFail($id);

        return view('produtos.edit', ['user' => $user, 'product' => $product]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        
        //Image Upload
        if($request->image) {

            $apagarFotos  = FotoProduto::where('id_produto', $request->id)->get();
            foreach($apagarFotos as $apagarFoto){
                $apagarFoto->delete();
            }

            foreach($request['image'] as $imagem){

                $requestImage = $imagem;
                $extension = $requestImage->extension();
                $imageName = md5($requestImage->getClientOriginalName() . strtotime("now")) . "." . $extension;
                $requestImage->move(public_path('img/produtos'), $imageName);

                $fotoProduto = new FotoProduto;
                $fotoProduto->id_produto = $request->id;
                $fotoProduto->path = $imageName;
                $fotoProduto->save();

            }

        }

        $product = Product::findOrFail($request->id);
        $product->title = $request->title;
        $product->description = $request->description;
        $product->preco = $request->preco;
        $product->token_pagamento = $request->token_pagamento;
        $product->user_id = $user->id;
        $product->save();

        return redirect('/produtos')->with('msg', 'Produto editado com sucesso!');

    }

    public function destroy($id)
    {
        $likes = Like::where('product_id', $id)->get();
        foreach($likes as $like){
            $like->delete();
        }

        $favorites = Favorite::where('product_id', $id)->get();
        foreach($favorites as $favorite){
            $favorite->delete();
        }

        $fotos = FotosProdutos::where('id_produto', $id)->get();
        foreach($fotos as $foto){
            $foto->delete();
        }

        Product::findOrFail($id)->delete();

        return redirect('/produtos')->with('msg', 'Produto excluido com sucesso!');
    }

    public function dashboard()
    {
        $user = auth()->user();

        if($user->adms > 0){
            $products = Product::all();
            return view('produtos.dashboard', ['user' => $user, 'products' => $products]);
        }else{
            return redirect('/');
        }


    }

    public function joinProduct($id)
    {
        $user = auth()->user();

        $user->productsAsParticipant()->attach($id);

        return redirect('/')->with('click', $id);

    }

    public function favorito_novo($id)
    {
        $user = auth()->user();

        $novo = New Favorite;
        $novo->product_id = $id;
        $novo->user_id = $user->id;

        $novo->save();

        return redirect('/')->with('click', $id);

    }

    public function usuarios()
    {
        $user = auth()->user();

        if($user->adms > 1){

            $users = User::where('id', '>', 0)->orderBy('adms', 'desc')->get();

            return view('usuarios', compact('users', 'user'));
        }else{
            return redirect('/');
        }

    }

    public function usuario_acao_promover($id, Request $request)
    {
        $user = User::findOrFail($id);

        if($user->adms < 2){
            $user->adms = $user->adms + 1;
            $user->save();
        }

        return redirect()->back();

    }
    public function usuario_acao_rebaixar($id, Request $request)
    {
        $user = User::findOrFail($id);

        if($user->adms > 0){
        $user->adms = $user->adms - 1;
        $user->save();
        }

        return redirect()->back();

    }

    public function removeFotoPerfil($id)
    {
        $user = auth()->user();

        if($id == $user->id){

            $update = User::findOrFail($id);
            $update->profile_photo_path = null;
            $update->save();

            return redirect()->back();
        }else{
            return redirect('/');
        }
    }


    public function emailEnvia()
    {

        $codigo = rand(1000, 9999);
        $email = '';
        
        $this->emailVerifica($codigo, $email);



        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions

        try {

            $mail->CharSet = 'UTF-8';
            //Server settings
            $mail->SMTPDebug = 0;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            //$mail->isMail();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'castrosandro2@gmail.com';                 // SMTP username
            $mail->Password = 'aiafiyemleynwuhe';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('castrosandro2@gmail.com', '3DPrintEvolution');

            $mail->addAddress($email);               // Name is optional
            //$mail->addReplyTo('info@example.com', 'Information');
            //$mail->addCC('cc@example.com');
            // $mail->addBCC('fabio@oncore.com.br');
            // $mail->AddAttachment($nome_excel);
            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Verificação de Email';
            $mail->Body    = '<p>Olá,</p>

            <p>Seu código de verificação é:</p>
            <br>
            <h1 style="color: green; font-weight: bold; text-align: center; font-size: 50px;">'.$codigo.'</h1>';

            //web$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();


        } catch (Exception $e) {


            echo '<br>Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }   


    }


    public function time($id)
    {

        if($id <= 300){
            $tempo = date('i:s', 300 - $id);
        }else{
            $tempo = date('i:s', 300 - 300);
        } 

        return $tempo;

    }

    
    


}
