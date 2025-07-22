Vorrei far diventare questo tool che applica una serie di constesti behat per scrivere scenari di validazione
dell'architettura software di un sistema, una estensione per il tool behat in modo da permettere all'utente di 
sovrascrivere tramite la configurazione dell'estensione alcuni parametri.

La root sará

"arkitect"

che avrá come figli:

1) project_root. (default %paths.base%). Valore stringa che indicherá la root del progetto
2) paths. default array vuoto. sará l'array di paths relativi dove il tool deve analizzare e validare le regole
3) excludes. default array vuoto. sará l'array di paths relativi dove il tool non fará nessuna analisi. Escluderá questi percorsi dalla validazione
4) ignore_errors. default array vuoto. sará un array di regex che andrá a filtrare gli errori trovati, mantenedo come errori validi i restanti.

Sará poi da modificare questo applicativo in base a questa nuova configurazione e fare i relativi test automatici.

Sará poi da modificare il read me in modo da informare l'utente su come configurare il tool.