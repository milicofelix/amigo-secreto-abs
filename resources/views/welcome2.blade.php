<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Setembro de Ofertas - Página 2</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <style>
    :root{
      --green:#16823a;
      --dark-green:#0f5f2a;
      --accent:#f2bf00;
      --card-bg:#fff;
      --muted:#666;
    }
    body{
      margin:0;
      font-family:"Roboto",sans-serif;
      background:#f5f5f5;
      color:#222;
    }
    .container{
      max-width:1100px;
      margin:20px auto;
      background:#fff;
      border-radius:6px;
      overflow:hidden;
      box-shadow:0 6px 20px rgba(0,0,0,0.08);
    }
    /* Header */
    .header{
      background:linear-gradient(180deg,var(--green),var(--dark-green));
      padding:24px;
      color:#fff;
      display:flex;
      align-items:center;
      justify-content:space-between;
      flex-wrap:wrap;
    }
    .header-left{
      display:flex;
      align-items:center;
      gap:16px;
    }
    .badge{
      width:120px;height:120px;
      border-radius:50%;
      background: radial-gradient(circle at 30% 30%, #ffd96a, #f2bf00 40%, #d18c00 100%);
      display:flex;flex-direction:column;align-items:center;justify-content:center;
      font-family:"Montserrat",sans-serif;
      font-weight:700;
      color:#fff;
      text-align:center;
      box-shadow:0 6px 12px rgba(0,0,0,0.2);
    }
    .badge div:nth-child(1){font-size:16px}
    .badge div:nth-child(2){font-size:26px}
    .header-right img{max-height:100px}
    /* Products grid */
    .products{
      display:grid;
      grid-template-columns:repeat(4,1fr);
      gap:16px;
      padding:20px;
    }
    @media(max-width:900px){
      .products{grid-template-columns:repeat(2,1fr);}
    }
    @media(max-width:600px){
      .products{grid-template-columns:1fr;}
    }
    .card{
      background:var(--card-bg);
      border:1px solid #eee;
      border-radius:6px;
      padding:12px;
      text-align:center;
      position:relative;
      display:flex;
      flex-direction:column;
      justify-content:space-between;
    }
    .imgwrap{
      height:120px;
      margin-bottom:10px;
      display:flex;
      align-items:center;
      justify-content:center;
      background:#fafafa;
      border-radius:6px;
    }
    .imgwrap img{max-height:100%;max-width:100%}
    .title{font-weight:700;font-size:14px;min-height:38px}
    .subtitle{font-size:12px;color:var(--muted);margin:6px 0}
    .price{font-family:"Montserrat",sans-serif;font-size:18px;font-weight:800;color:#111}
    .cashback{font-size:12px;color:var(--green);font-weight:700;margin-top:4px}
    .free-ship{
      position:absolute;
      top:8px;left:8px;
      background:#111;color:#fff;
      font-size:10px;padding:4px 6px;border-radius:4px;
    }
    /* Footer */
    .footer{
      background:var(--green);
      color:#fff;
      padding:18px;
      display:flex;
      flex-wrap:wrap;
      align-items:center;
      justify-content:space-between;
      gap:12px;
    }
    .qr{background:#fff;border-radius:6px;overflow:hidden;width:90px;height:90px;display:flex;align-items:center;justify-content:center}
    .info{font-size:13px}
    .hours{font-size:12px;margin-top:4px}
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="header-left">
        <div class="badge">
          <div>SETEMBRO</div>
          <div>Ofertas</div>
        </div>
        <div>
          <h2 style="margin:0;font-family:'Montserrat',sans-serif;font-size:22px">Setembro de Ofertas</h2>
          <p style="margin:6px 0 0;font-size:13px;max-width:380px">Lançamentos e descontos especiais para você aproveitar este mês.</p>
        </div>
      </div>
      <div class="header-right">
        <img src="https://picsum.photos/seed/produtos/200/120" alt="Produtos destaque">
      </div>
    </div>

    <div class="products">
      <!-- Produto exemplo -->
      <div class="card">
        <div class="free-ship">FRETE GRÁTIS</div>
        <div class="imgwrap">
          <img src="https://picsum.photos/seed/vinagre/200/150" alt="">
        </div>
        <div class="title">Vinagre de Maçã Orgânico 500ml</div>
        <div class="price">R$32,32</div>
        <div class="cashback">+10% de CASHBACK</div>
      </div>

      <div class="card">
        <div class="free-ship">FRETE GRÁTIS</div>
        <div class="imgwrap"><img src="https://picsum.photos/seed/oleo/200/150"></div>
        <div class="title">Óleo de Coco Extra Virgem 200ml</div>
        <div class="price">R$28,00</div>
        <div class="cashback">+10% de CASHBACK</div>
      </div>

      <div class="card">
        <div class="free-ship">FRETE GRÁTIS</div>
        <div class="imgwrap"><img src="https://picsum.photos/seed/mel/200/150"></div>
        <div class="title">Mel Silvestre 1kg</div>
        <div class="price">R$25,35</div>
        <div class="cashback">+10% de CASHBACK</div>
      </div>

      <div class="card">
        <div class="free-ship">FRETE GRÁTIS</div>
        <div class="imgwrap"><img src="https://picsum.photos/seed/tapioca/200/150"></div>
        <div class="title">Tapioca Pronta da Terra 500g</div>
        <div class="price">R$5,60</div>
        <div class="cashback">+10% de CASHBACK</div>
      </div>

      <!-- Você pode repetir os cards para os outros produtos (aveia, castanhas, sementes, farinhas etc.) -->
    </div>

    <div class="footer">
      <div style="display:flex;gap:12px;align-items:center">
        <div class="qr">
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://graonutriworld.com.br" alt="QR" width="90" height="90">
        </div>
        <div class="info">
          <div><strong>Peça pelo WhatsApp:</strong> (11) 94729-7718</div>
          <div class="hours">
            Seg-Sex: 9h às 21h | Sáb-Dom: 10h às 14h | Feriados: 9h às 16h
          </div>
        </div>
      </div>
      <div style="font-weight:700">Grão Delivery</div>
    </div>
  </div>
</body>
</html>
