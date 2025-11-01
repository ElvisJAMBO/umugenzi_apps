<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tickets</title>
    <style>
        /* CSS adapté pour dompdf - Utilisez des styles simples */
        body {
            font-family: 'DejaVu Sans', sans-serif; /* DejaVu Sans est souvent nécessaire pour les PDF */
            margin: 0;
            padding: 0;
            font-size: 10px; /* Taille de base plus petite pour le PDF */
        }

        :root {
            --main-color: #0c183a; /* Bleu marine foncé */
            --accent-color: #f7a040; /* Orange/Jaune */
            --paper-color: #f0f0f0; /* Fond de page */
        }

        .ticket-container {
            width: 200mm; /* Largeur fixe, env. 210mm total pour A4, ici pour le ticket */
            height: 70mm;  /* Hauteur fixe */
            margin: 10mm auto; /* Centrer sur la page */
            background-color: #0c183a; /* Couleur principale */
            color: #ffffff; /* Texte blanc */
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .background-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.2; /* Transparence de l'image (l'image sera visible sous la couleur du conteneur) */
            z-index: 1; /* Reste en dessous du contenu */
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center center;
        }
        
        /* Placeholder pour le fond (REMPLACEZ L'URL ABSOLUE PAR VOTRE IMAGE EN BASE64 OU URL COMPLÈTE) */
        /* Exemple de fond en base64 (plus fiable pour Dompdf) */
        

        /* ------------------ STRUCTURE DU TICKET ------------------ */
        .ticket-main, .ticket-stub {
            float: left;
            height: 100%;
            box-sizing: border-box;
            padding: 15px;
            position: relative; /* Nécessaire pour positionner le contenu au-dessus du background-overlay */
            z-index: 5; 
        }

        .ticket-main {
            width: 70%;
            background-color: rgba(12, 24, 58, 0.9); /* Utilisez une opacité légère pour laisser passer le fond */
        }

        .ticket-stub {
            width: 30%;
            border-left: 1px dashed #ffffff;
            text-align: center;
            background-color: rgba(12, 24, 58, 0.85); /* Légèrement plus foncé */
        }
        
        /* ------------------ MISE EN PAGE INTERNE DU MAIN ------------------ */
        .main-content {
            /* Contient le titre, l'organisateur, et les infos */
            float: left;
            width: 60%; /* Espace pour le texte */
        }
        
        .qr-code-wrapper {
            /* Contient le QR code et le texte d'instruction */
            float: right;
            width: 38%; /* Espace pour le QR code */
            text-align: center;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* ------------------ TYPOGRAPHIE ET ELEMENTS ------------------ */
        .title { font-size: 2.4em; font-weight: bold; margin: 0 0 5px 0; text-transform: uppercase; }
        .organizer { font-size: 1.4em; opacity: 0.8; margin-bottom: 10px; }
        .info-block p { margin: 3px 0; font-size: 1.4em; }
        .datetime { font-weight: bold; font-size: 1.4em; }

        /* QR CODE (dans le wrapper) */
        .qr-code img {
            /* ⚠️ Taille augmentée pour le ticket-main */
            width: 180px; 
            height: 180px;
            background-color: white; 
            padding: 5px;
            border-radius: 3px;
        }

        .scan-text { font-size: 0.7em; opacity: 0.9; margin-top: 5px; }

        /* STUB */
        .stub-content {
            /* Centrer le contenu du stub */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
        }
        .stub-header { font-size: 1.4em; line-height: 1.2; margin-bottom: 10px; }
        .stub-label { font-size: 1.2em; opacity: 0.6; margin: 5px 0 0 0; }
        .ticket-number { font-size: 1.5em; font-weight: bold; margin: 0 0 10px 0; color: var(--accent-color); }
        
        /* QR CODE dans le stub (taille réduite) */
        .ticket-stub .qr-code img {
            width: 100px; /* Taille réduite */
            height: 100px;
        }

        /* ------------------ EFFETS DIVERS ------------------ */
        .ticket-stub::before, .ticket-stub::after {
            content: ''; position: absolute; width: 10px; height: 10px; border-radius: 50%; 
            background-color: var(--paper-color); left: -5px; z-index: 10;
        }
        .ticket-stub::before { top: -5px; }
        .ticket-stub::after { bottom: -5px; }

    </style>
</head>
<body>
    @foreach($ticketInstances as $ticketInstance)
    <div class="ticket-container">
        <div class="ticket-main">
            <div class="background-overlay" style="background-image: url('/image_events/{{ $ticketInstance->reservation->ticket->typeticket->evenement->image }}');"></div>
            
            <div class="clearfix" style="position: relative; z-index: 10;">
                <div class="main-content">
                    <h1 class="title">{{ strtoupper($ticketInstance->reservation->ticket->typeticket->evenement->titre) }}</h1>
                    <p class="organizer">Organisé par : {{ $ticketInstance->reservation->ticket->typeticket->evenement->user->name }}</p>
                    
                    <div class="info-block">
                        <p class="datetime">
                            {{ \Carbon\Carbon::parse($ticketInstance->reservation->ticket->typeticket->evenement->date_event)->format('d/m/Y') }} 
                            @if ($ticketInstance->reservation->ticket->typeticket->evenement->heure)
                                à {{ \Carbon\Carbon::parse($ticketInstance->reservation->ticket->typeticket->evenement->heure)->format('H:i') }}
                            @endif
                        </p>
                        <p class="location">Lieu : {{ $ticketInstance->reservation->ticket->typeticket->evenement->place }}</p>
                        <p>Type : {{ $ticketInstance->reservation->ticket->typeticket->nom }}</p>
                    </div>
                </div>

                <div class="qr-code-wrapper">
                    <div class="qr-code">
                        <img src="{{ $ticketInstance->qr_code }}" 
                            alt="Code QR du billet" 
                            style="width: 180px; height: 180px; padding: 5px; background-color: white;">
                    </div>
                    <p class="scan-text">SCANNEZ À L'ENTRÉE</p>
                </div>
            </div>
        </div>

        <div class="ticket-stub">
            <div class="background-overlay" style="background-image: url('/image_events/1757316031.téléchargement.jpeg');"></div>
            
            <div class="stub-content" style="position: relative; z-index: 10;">
                <p class="stub-header">{{ strtoupper($ticketInstance->reservation->ticket->typeticket->evenement->titre) }}<br>
                    {{ \Carbon\Carbon::parse($ticketInstance->reservation->ticket->typeticket->evenement->date_event)->format('d/m/Y') }}</p>
                
                <p class="stub-label">BILLET N°:</p>
                <p class="ticket-number">{{ $ticketInstance->id }}</p>
                
                <div class="qr-code">
                    <img src="{{ $ticketInstance->qr_code }}" 
                        alt="Code QR du billet" 
                        style="width: 100px; height: 100px; padding: 3px; background-color: white;">
                </div>
            </div>
        </div>
    </div>
    @endforeach
</body>
</html>