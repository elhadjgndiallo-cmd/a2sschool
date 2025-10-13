{{-- Version de test ultra-simple --}}
<!DOCTYPE html>
<html>
<head>
    <title>Test Step 4</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
    </style>
</head>
<body>
    <h1>TEST ULTRA SIMPLE - OPTIONS DE PAIEMENT SPÉCIALES</h1>
    
    <div style="background-color: #ff0000; color: white; padding: 50px; margin: 20px 0; border: 10px solid #000000; font-size: 30px; text-align: center; font-weight: bold;">
        <h1 style="color: white; font-size: 40px; margin-bottom: 30px;">
            🎁 TEST ULTRA SIMPLE - OPTIONS DE PAIEMENT SPÉCIALES 🎁
        </h1>
        
        <div style="background-color: white; color: black; padding: 30px; margin: 20px 0; border: 5px solid #000000;">
            <input type="hidden" name="gratuit_inscription" value="0">
            <input type="checkbox" name="gratuit_inscription" value="1" 
                   id="gratuit_inscription" 
                   style="transform: scale(3); margin-right: 20px;">
            <label for="gratuit_inscription" style="font-size: 24px; font-weight: bold;">
                🎁 INSCRIPTION GRATUITE 🎁
            </label>
        </div>

        <div style="background-color: white; color: black; padding: 30px; margin: 20px 0; border: 5px solid #000000;">
            <input type="hidden" name="gratuit_reinscription" value="0">
            <input type="checkbox" name="gratuit_reinscription" value="1" 
                   id="gratuit_reinscription" 
                   style="transform: scale(3); margin-right: 20px;">
            <label for="gratuit_reinscription" style="font-size: 24px; font-weight: bold;">
                🎁 RÉINSCRIPTION GRATUITE 🎁
            </label>
        </div>
    </div>
    
    <p>Si vous voyez cette section rouge, le problème vient du rendu Laravel complexe.</p>
    <p>Si vous ne voyez pas cette section rouge, le problème vient du CSS ou du navigateur.</p>
</body>
</html>