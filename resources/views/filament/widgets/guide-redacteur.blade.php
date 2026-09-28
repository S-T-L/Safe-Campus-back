@php
    use App\Filament\Resources\HistoireResource;
@endphp

<x-filament-widgets::widget>
    <x-filament::section collapsible collapsed>
        <x-slot name="heading">
            Guide rapide — Rédacteur
        </x-slot>

        <div class="prose prose-sm dark:prose-invert max-w-none">
            <p>Tu es rédacteur. Tu écris les histoires interactives (mode histoire) affichées sur le site.</p>

            <p>
                <strong>Histoires</strong>
                <x-filament::link :href="HistoireResource::getUrl()" icon="heroicon-o-arrow-right" icon-position="after" size="xs">
                    Ouvrir la page Histoires
                </x-filament::link>
            </p>
            <ul>
                <li>Une histoire est un parcours interactif à choix multiples : le visiteur avance de scène en scène et peut arriver à une fin favorable ou défavorable.</li>
                <li>Le champ <em>État</em> suit le circuit éditorial : <em>Brouillon</em> → <em>Relecture</em> → <em>Validé</em> → <em>Publié</em>. Seule une histoire à l'état <em>Publié</em> apparaît sur le site.</li>
                <li>Tant que l'histoire n'est pas prête, laisse-la en <em>Brouillon</em> ou <em>Relecture</em> : elle reste invisible pour les visiteurs, tu peux la modifier tranquillement.</li>
                <li>L'onglet <em>Sous-thèmes</em> rattache l'histoire à une ou plusieurs rubriques (bouton <em>Attacher</em>). C'est ce rattachement qui fait apparaître le bouton « Suivre l'histoire » sur la page ressources correspondante.</li>
            </ul>

            <p><strong>Scènes</strong> (onglet <em>Scènes</em> de la fiche histoire)</p>
            <ul>
                <li>Chaque scène a un <em>Dialogue</em> : c'est le seul texte affiché au visiteur. Le <em>Titre</em> et l'<em>Ordre</em> restent réservés au back-office.</li>
                <li>L'interrupteur <em>Scène initiale</em> marque le point de départ du parcours. Une seule scène par histoire doit être initiale.</li>
                <li>Le <em>Média</em> (image ou fond) est optionnel : il faut d'abord l'avoir ajouté depuis la page Médias.</li>
                <li>Le <em>Sous-thème (bifurcation)</em> ne sert que si cette scène précise fait basculer l'histoire vers une autre thématique en cours de route — laisse-le vide sinon.</li>
            </ul>

            <p><strong>Choix</strong> (dans chaque scène, section <em>Choix</em>)</p>
            <ul>
                <li>Chaque choix a un texte, puis soit une <em>Scène suivante</em> (le parcours continue), soit une <em>Issue</em> (le parcours s'arrête ici) — jamais les deux à la fois.</li>
                <li>Une issue <em>Favorable</em> ou <em>Défavorable</em> ne se renseigne que sur un choix qui termine le parcours.</li>
                <li>Sur une fin <em>Défavorable</em>, les contacts utiles s'affichent automatiquement au visiteur, à partir des sous-thèmes de l'histoire — pas besoin de les choisir toi-même.</li>
            </ul>

            <p>
                <strong>Prévisualiser</strong>
            </p>
            <ul>
                <li>Le bouton <em>Prévisualiser</em>, sur la liste des histoires, permet de rejouer le parcours clic par clic comme un visiteur, avant publication.</li>
                <li>Utilise-le pour vérifier que chaque choix mène à la bonne scène et que les fins défavorables affichent les bons contacts.</li>
            </ul>

            <p class="text-xs opacity-70">Pour retirer une histoire du site sans perdre ton travail, repasse simplement son état en <em>Brouillon</em> plutôt que de la supprimer.</p>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
