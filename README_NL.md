![CardGate](https://cdn.curopayments.net/thumb/200/logos/cardgate.png)

# CardGate module voor xtCommerce

[![Total Downloads](https://img.shields.io/packagist/dt/cardgate/xtcommerce.svg)](https://packagist.org/packages/cardgate/xtcommerce)
[![Latest Version](https://img.shields.io/packagist/v/cardgate/xtcommerce.svg)](https://github.com/cardgate/xtcommerce/releases)
[![Build Status](https://travis-ci.org/cardgate/xtcommerce.svg?branch=master)](https://travis-ci.org/cardgate/xtcommerce)

## Support

Deze plug-in is geschikt voor xtCommerce versies **4** and **5**.

## Voorbereiding

Voor het gebruik van deze module zijn CardGate RESTful gegevens nodig.  
Bezoek hiervoor [Mijn CardGate](https://my.cardgate.com/) en haal daar je  
RESTful API gebruikersnaam en wachtwoord op, of neem contact op met je accountmanager.

## Installatie

1. Download en unzip het **xtcommerce.zip** bestand op je bureaublad.

2. Plaats de **inhoud** van de **xtcommerce** map in de root van je website.

## Configuratie

1. Ga naar de **admin** van je website, en kies **Plugins, gedeïnstalleerde plugins.**

2. Scroll naar de Module **Payment**, en kies de **CardGate** plug-in. (Mogelijk staat hij op de volgende pagina).

3. Klik op **Run**, bij **Actions**, zodat de plug-in geïnstalleerd wordt.

4. Doe dit ook voor de CardGate **betaalmethoden** die je wilt gaan gebruiken in je shop.

5. De gekozen plug-ins staan nu bij **Geïnstalleerde plugins.**

6. Vink daar de CardGate plug-ins aan, en kies **Keuze activeren**, zodat ze geactiveerd worden.

7. Kies in het linkermenu voor **Instellingen** en dan **Betaalmethoden.**

8. Kies de CardGate plug-in en klik op **Bewerken**.

9. Vul nu de **Merchant API key**, de **Merchant ID**, de **Hash key (Codeersleutel)** en de **Site ID** in zoals die aan je  
   toegestuurd zijn via het bericht "Installeren webshop-kassa" in [Mijn CardGate](https://my.cardgate.com/).

10. Vul ook de andere waarden in indien nodig, en sla het op.  
   **LET WEL:** De CardGate plug-in moet **niet geactiveerd** worden en wordt alleen gebruikt om de instellingen op te slaan die de CardGate betaalmethoden gebruiken.

11. Vink de **CardGate betaalmethoden** aan die je wilt gebruiken en klik op **Keuze activeren**.

12. Zorg ervoor dat je na het testen bij **Configuratie** in de **CardGate** module de **Test Mode** omschakelt van **true** naar **false** en sla het op (**Save**).

## Vereisten

Geen verdere vereisten.
