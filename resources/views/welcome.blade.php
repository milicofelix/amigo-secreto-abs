<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Setembro de Ofertas - Mockup</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    :root{
      --green:#16823a;
      --dark-green:#0f5f2a;
      --accent:#f2bf00;
      --light:#fafafa;
      --card-bg:#ffffff;
      --muted:#666;
    }
    *{box-sizing:border-box}
    body{
      font-family: "Roboto", Arial, sans-serif;
      margin:0;
      background: linear-gradient(#fff,#f5f5f5);
      color:#222;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }

    .container{
      max-width:1100px;
      margin:24px auto;
      background: #fff;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
      border-radius:6px;
      overflow:hidden;
    }

    /* Header: top green band */
    .top{
      background: linear-gradient(180deg,var(--green),var(--dark-green));
      color: white;
      padding:24px;
      display:flex;
      gap:20px;
      align-items:center;
      justify-content:space-between;
      flex-wrap:wrap;
    }

    .promo-left{
      display:flex;
      gap:18px;
      align-items:center;
      min-width:260px;
    }
    .badge-circle{
      width:120px;
      height:120px;
      background: radial-gradient(circle at 30% 30%, #ffd96a, #f2bf00 40%, #d18c00 100%);
      border-radius:50%;
      box-shadow: 0 6px 12px rgba(0,0,0,0.2);
      display:flex;
      align-items:center;
      justify-content:center;
      flex-direction:column;
      font-family: "Montserrat",sans-serif;
      color:#fff;
      text-align:center;
    }
    .badge-circle .line1{font-size:18px;font-weight:700;letter-spacing:1px}
    .badge-circle .line2{font-size:30px;font-weight:800}

    .promo-right{
      display:flex;
      gap:14px;
      align-items:center;
      justify-content:flex-end;
      text-align:right;
    }
    .prime-box{
      background: rgba(255,255,255,0.06);
      padding:12px 16px;
      border-radius:8px;
      min-width:200px;
    }
    .prime-title{font-weight:700;font-family:"Montserrat",sans-serif;font-size:18px}
    .prime-sub{font-size:13px; color: #e9f5e9; margin-top:6px}

    /* Product grid */
    .products{
      display:grid;
      grid-template-columns: repeat(3,1fr);
      gap:18px;
      padding:22px;
      background:linear-gradient(#fff,#fff);
    }
    @media (max-width:900px){
      .products{grid-template-columns: repeat(2,1fr)}
    }
    @media (max-width:600px){
      .products{grid-template-columns: 1fr}
    }

    .card{
      background:var(--card-bg);
      border-radius:8px;
      padding:12px;
      border:1px solid #eee;
      display:flex;
      flex-direction:column;
      align-items:center;
      text-align:center;
      position:relative;
      min-height:320px;
    }
    .card .imgwrap{
      width:100%;
      height:160px;
      border-radius:6px;
      overflow:hidden;
      display:flex;
      align-items:center;
      justify-content:center;
      background:#f8f8f8;
      margin-bottom:12px;
    }
    .card img{max-width:100%; max-height:100%; object-fit:cover}

    .free-ship{
      position:absolute;
      top:10px;
      left:10px;
      background:#111;
      color:#fff;
      font-size:11px;
      padding:6px 8px;
      border-radius:4px;
      font-weight:600;
    }

    .title{
      font-weight:700;
      font-size:15px;
      min-height:40px;
    }
    .subtitle{font-size:12px;color:var(--muted); margin-top:6px}

    .price-row{
      margin-top:auto;
      width:100%;
      padding-top:12px;
      display:flex;
      gap:8px;
      align-items:baseline;
      justify-content:center;
      flex-direction:column;
    }
    .old-price{font-size:12px;color:var(--muted); text-decoration:line-through}
    .price{
      font-family:"Montserrat",sans-serif;
      font-size:22px;
      color:#111;
      font-weight:800;
    }
    .cashback{
      margin-top:6px;
      font-size:13px;
      color:var(--green);
      font-weight:700;
    }

    /* second row with different layout: show single product wide etc. We'll create variety by CSS classes */
    .card.small{
      min-height:240px;
    }

    /* Footer */
    .footer{
      background:var(--green);
      color:#fff;
      padding:18px 22px;
      display:flex;
      gap:12px;
      align-items:center;
      justify-content:space-between;
      flex-wrap:wrap;
    }
    .footer .left{display:flex; gap:12px; align-items:center}
    .qr{
      width:92px;
      height:92px;
      background:#fff;
      display:flex;
      align-items:center;
      justify-content:center;
      border-radius:6px;
      overflow:hidden;
    }
    .addresses{font-size:13px}
    .cards{
      display:flex;
      gap:8px;
      align-items:center;
      font-size:12px;
      opacity:0.95;
    }

    /* small helpers */
    .label {
      display:inline-block;
      padding:6px 8px;
      border-radius:18px;
      background:#f5f5f5;
      font-size:12px;
      color:#333;
      margin-top:8px;
    }

    /* little ribbon for promotion */
    .ribbon{
      position:absolute;
      top:10px;
      right:10px;
      background:var(--accent);
      color:#111;
      padding:6px 10px;
      font-weight:800;
      border-radius:4px;
      font-size:12px;
    }

    /* subtle print-like fold line */
    .fold {
      height:8px;
      background:linear-gradient(90deg, rgba(0,0,0,0.03), rgba(0,0,0,0.01));
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="top">
      <div class="promo-left">
        <div class="badge-circle">
          <div class="line1">SETEMBRO</div>
          <div class="line2">de</div>
          <div class="line1" style="font-size:20px;margin-top:3px">Ofertas</div>
        </div>
        <div style="color:#fff; max-width:520px">
          <h2 style="margin:0;font-family:'Montserrat',sans-serif;font-size:28px; font-weight:800">Setembro de Ofertas</h2>
          <p style="margin:6px 0 0;color:rgba(255,255,255,0.95)">Promoções selecionadas para você aproveitar. Confira os preços e o cashback exclusivo.</p>
        </div>
      </div>

      <div class="promo-right">
        <div class="prime-box">
          <div class="prime-title">CashBack Prime Club</div>
          <div class="prime-sub">Seu dinheiro de volta na sua próxima compra.</div>
        </div>
        <div style="min-width:120px; text-align:right">
          <div class="label" style="background:rgba(255,255,255,0.12); color:#fff">Frete Grátis</div>
        </div>
      </div>
    </div>

    <div class="products" aria-label="Produtos em oferta">
      <!-- Card 1 -->
      <div class="card">
        <div class="free-ship">FRETE GRÁTIS</div>
        <div class="ribbon">-46%</div>
        <div class="imgwrap">
          <img src="https://picsum.photos/seed/whey/600/400" alt="Produto 1">
        </div>
        <div class="title">Combo Whey 100% + Creatina</div>
        <div class="subtitle">900g + 300g - IntegralMedica</div>

        <div class="price-row">
          <div class="old-price">DE: R$319,90</div>
          <div class="price">R$169,90</div>
          <div class="cashback">+10% de CASHBACK</div>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="card small">
        <div class="free-ship">FRETE GRÁTIS</div>
        <div class="imgwrap">
          <img src="https://picsum.photos/seed/creatina/600/400" alt="Produto 2">
        </div>
        <div class="title">Creatina MWB 300g</div>
        <div class="subtitle">Monohidrato de creatina - Pure</div>

        <div class="price-row">
          <div class="old-price">DE: R$149,90</div>
          <div class="price">R$79,90</div>
          <div class="cashback">+10% de CASHBACK</div>
        </div>
      </div>

      <!-- Card 3 -->
      <div class="card">
        <div class="free-ship">FRETE GRÁTIS</div>
        <div class="imgwrap">
          <img src="https://picsum.photos/seed/hiper/600/400" alt="Produto 3">
        </div>
        <div class="title">Hipercalórico MonsterOne 3kg</div>
        <div class="subtitle">Ganho de massa</div>

        <div class="price-row">
          <div class="old-price">DE: R$215,15</div>
          <div class="price">R$124,90</div>
          <div class="cashback">+10% de CASHBACK</div>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="card small">
        <div class="free-ship">FRETE GRÁTIS</div>
        <div class="imgwrap">
          <img src="https://picsum.photos/seed/pre/600/400" alt="Produto 4">
        </div>
        <div class="title">Pré-treino Psycho Vortex 300g</div>
        <div class="subtitle">Efeito estimulante</div>

        <div class="price-row">
          <div class="old-price">DE: R$179,90</div>
          <div class="price">R$89,90</div>
          <div class="cashback">+10% de CASHBACK</div>
        </div>
      </div>

      <!-- Card 5 -->
      <div class="card">
        <div class="free-ship">FRETE GRÁTIS</div>
        <div class="imgwrap">
          <img src="https://picsum.photos/seed/burn/600/400" alt="Produto 5">
        </div>
        <div class="title">Termogênico PsycoBurn 60 cápsulas</div>
        <div class="subtitle">Queima de gordura</div>

        <div class="price-row">
          <div class="old-price">DE: R$199,90</div>
          <div class="price">R$59,90</div>
          <div class="cashback">+10% de CASHBACK</div>
        </div>
      </div>

      <!-- Card 6 -->
      <div class="card">
        <div class="imgwrap">
          <img src="https://picsum.photos/seed/pasta/600/400" alt="Produto 6">
        </div>
        <div class="title">Pasta de Amendoim Power One 1kg</div>
        <div class="subtitle">Sabor tradicional</div>

        <div class="price-row">
          <div class="old-price">DE: R$34,90</div>
          <div class="price">R$24,90</div>
          <div class="cashback">+10% de CASHBACK</div>
        </div>
      </div>

      <!-- Card 7 -->
      <div class="card">
        <div class="imgwrap">
          <img src="https://picsum.photos/seed/lanexa/600/400" alt="Produto 7">
        </div>
        <div class="title">Pasta de Amendoim La Ganexa 450g</div>
        <div class="subtitle">Vários sabores</div>

        <div class="price-row">
          <div class="old-price">1 UNIDADE: R$44,90</div>
          <div class="price">R$32,90 <span style="font-size:12px;color:var(--muted)"> (2ª unid. menor)</span></div>
          <div class="cashback">+10% de CASHBACK</div>
        </div>
      </div>

      <!-- Card 8 -->
      <div class="card small">
        <div class="free-ship">FRETE GRÁTIS</div>
        <div class="imgwrap">
          <img src="https://picsum.photos/seed/panqueca/600/400" alt="Produto 8">
        </div>
        <div class="title">Pouch Panqueca Panquecas</div>
        <div class="subtitle">600g - La Ganexa</div>

        <div class="price-row">
          <div class="old-price">DE: R$89,90</div>
          <div class="price">R$54,90</div>
          <div class="cashback">+10% de CASHBACK</div>
        </div>
      </div>

      <!-- Card 9 (extra illustrative) -->
      <div class="card">
        <div class="imgwrap">
          <img src="https://picsum.photos/seed/snack/600/400" alt="Produto 9">
        </div>
        <div class="title">Snack Proteico - Letinho</div>
        <div class="subtitle">40g - 10 unidades</div>

        <div class="price-row">
          <div class="old-price">DE: R$69,90</div>
          <div class="price">R$39,90</div>
          <div class="cashback">+10% de CASHBACK</div>
        </div>
      </div>

    </div>

    <div class="fold"></div>

    <div class="footer">
      <div class="left">
        <div class="qr">
          <!-- placeholder qr -->
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://graonutriworld.com.br" alt="QR">
        </div>
        <div style="margin-left:10px">
          <div style="font-weight:700">graonutriworld.com.br</div>
          <div class="addresses">
            End. Unidade 1: Av. Nordestina, 65, São Miguel/SP<br>
            End. Unidade 2: Rua Tibúrcio de Sousa, 237 - Itaim Paulista
          </div>
        </div>
      </div>

      <div class="cards">
        <div>Aceitamos:</div>
        <img src="https://picsum.photos/seed/visa/80/28" alt="Visa" style="height:28px;border-radius:4px">
        <img src="https://picsum.photos/seed/master/80/28" alt="Mastercard" style="height:28px;border-radius:4px">
        <img src="https://picsum.photos/seed/elo/80/28" alt="Elo" style="height:28px;border-radius:4px">
      </div>
    </div>

  </div>
</body>
</html>
