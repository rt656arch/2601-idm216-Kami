<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, viewport-fit=cover"
    />
    <title>Home | Kami Food Truck</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Black+Han+Sans&family=DM+Sans:wght@400;500;600;700;800&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="css/_base.css" />
    <link rel="stylesheet" href="css/_chrome.css" />
    <link rel="stylesheet" href="css/_buttons.css" />
    <link rel="stylesheet" href="css/_cards.css" />
    <link rel="stylesheet" href="css/_home.css" />
    <link rel="stylesheet" href="css/_responsive.css" />
  </head>
  <body>
    <main>
      <header></header>
      <div><img src="images/background.png" alt="red" /></div>

      <br />
      <div class="greeting">
        <h1><span class="korean">안녕,</span> Hello!</h1>
      </div>
      <br />

      <!-- Popular carousel -->
      <h2 class="label">Popular:</h2>
      <div class="carousel">
        <a href="menu-item.php?id=6" class="slide active">
          <img src="images/tteokbokki-ellipse.png" alt="Tteokbokki" />
          <div class="slide-info">
            <div>
              <p class="korean-label">떡볶이</p>
              <p class="bold">Tteokbokki</p>
            </div>
            <span class="slide-price">$11</span>
          </div>
        </a>

        <a href="menu-item.php?id=1" class="slide">
          <img src="images/bibimbap-ellipse.png" alt="Bibimbap" />
          <div class="slide-info">
            <div>
              <p class="korean-label">비빔밥</p>
              <p class="bold">Bibimbap</p>
            </div>
            <span class="slide-price">$11</span>
          </div>
        </a>

        <a href="menu-item.php?id=2" class="slide">
          <img src="images/bulgogi-ellipse.png" alt="Bulgogi" />
          <div class="slide-info">
            <div>
              <p class="korean-label">불고기</p>
              <p class="bold">Bulgogi</p>
            </div>
            <span class="slide-price">$12</span>
          </div>
        </a>

        <a href="menu-item.php?id=5" class="slide">
          <img
            src="images/kimchi-fried-rice-ellipse.png"
            alt="Kimchi Fried Rice"
          />
          <div class="slide-info">
            <div>
              <p class="korean-label">김치볶음밥</p>
              <p class="bold">Kimchi Fried Rice</p>
            </div>
            <span class="slide-price">$11</span>
          </div>
        </a>

        <a href="menu-item.php?id=10" class="slide">
          <img src="images/mandu-ellipse.png" alt="Mandu" />
          <div class="slide-info">
            <div>
              <p class="korean-label">만두</p>
              <p class="bold">Mandu</p>
            </div>
            <span class="slide-price">$5</span>
          </div>
        </a>
      </div>

      <br />

      <!-- Worth Trying grid -->
      <div class="grid">
        <h2
          class="label"
          style="grid-column: 1 / -1; margin-left: 0; justify-self: start"
        >
          Worth Trying:
        </h2>

        <a href="menu-item.php?id=5" class="grid-item">
          <img
            src="images/kimchi-fried-rice-ellipse.png"
            alt="Kimchi Fried Rice"
          />
          <div>
            <p class="muted">김치볶음밥</p>
            <p class="bold">Kimchi Fried Rice</p>
            <p class="slide-price" style="text-align: right">$11</p>
          </div>
        </a>

        <a href="menu-item.php?id=8" class="grid-item">
          <img src="images/mandu-ellipse.png" alt="Ramen" />
          <div>
            <p class="muted">라면</p>
            <p class="bold">Ramen</p>
            <p class="slide-price" style="text-align: right">$9</p>
          </div>
        </a>

        <a href="menu-item.php?id=1" class="grid-item">
          <img src="images/bibimbap-ellipse.png" alt="Bibimbap" />
          <div>
            <p class="muted">비빔밥</p>
            <p class="bold">Bibimbap</p>
            <p class="slide-price" style="text-align: right">$11</p>
          </div>
        </a>

        <a href="menu-item.php?id=2" class="grid-item">
          <img src="images/bulgogi-ellipse.png" alt="Bulgogi" />
          <div>
            <p class="muted">불고기</p>
            <p class="bold">Bulgogi</p>
            <p class="slide-price" style="text-align: right">$12</p>
          </div>
        </a>
      </div>

      <br /><br />

      <nav>
        <a href="menu.php">
          <img src="images/navigation-bar-menu.png" alt="Menu" />
          <span>Menu</span>
        </a>
        <a href="home.html" class="active">
          <img src="images/navigation-bar-home.png" alt="Home" />
          <span>Home</span>
        </a>
        <a href="bag-empty.html">
          <img src="images/navigation-bar-bag.png" alt="Bag" />
          <span>Bag</span>
        </a>
      </nav>
    </main>
  </body>
</html>
