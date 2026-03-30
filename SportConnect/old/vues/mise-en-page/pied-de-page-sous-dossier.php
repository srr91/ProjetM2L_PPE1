    <footer style="background: var(--dark); color: white; padding: 3rem 0; margin-top: 5rem;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 3rem; margin-bottom: 2rem;">
                
                <!-- Section À propos -->
                <div>
                    <h3 style="color: var(--primary); margin-bottom: 1rem;">⚡ SportConnect</h3>
                    <p style="color: var(--gray); line-height: 1.6;">
                        Connectez-vous avec les meilleurs coachs sportifs pour atteindre vos objectifs.
                    </p>
                </div>

                <!-- Section Liens rapides -->
                <div>
                    <h4 style="color: white; margin-bottom: 1rem; font-size: 1.1rem;">Liens rapides</h4>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php $base = defined('BASE_PATH') ? BASE_PATH : '/SportConnect'; ?>
                        <li style="margin-bottom: 0.75rem;">
                            <a href="<?= $base ?>/index.php?route=coachs" style="color: var(--gray); text-decoration: none; transition: color 0.3s; cursor: pointer;" 
                               onmouseover="this.style.color='var(--primary)'" 
                               onmouseout="this.style.color='var(--gray)'">
                                Trouver un coach
                            </a>
                        </li>
                        <li style="margin-bottom: 0.75rem;">
                            <a href="<?= $base ?>/index.php?route=auth/register" style="color: var(--gray); text-decoration: none; transition: color 0.3s; cursor: pointer;" 
                               onmouseover="this.style.color='var(--primary)'" 
                               onmouseout="this.style.color='var(--gray)'">
                                Devenir coach
                            </a>
                        </li>
                        <li style="margin-bottom: 0.75rem;">
                            <a href="<?= $base ?>/index.php#apropos" style="color: var(--gray); text-decoration: none; transition: color 0.3s; cursor: pointer;" 
                               onmouseover="this.style.color='var(--primary)'" 
                               onmouseout="this.style.color='var(--gray)'">
                                À propos
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Section Support -->
                <div>
                    <h4 style="color: white; margin-bottom: 1rem; font-size: 1.1rem;">Support</h4>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li style="margin-bottom: 0.75rem;">
                            <a href="<?= $base ?>/pages/faq.php" style="color: var(--gray); text-decoration: none; transition: color 0.3s; cursor: pointer;" 
                               onmouseover="this.style.color='var(--primary)'" 
                               onmouseout="this.style.color='var(--gray)'">
                                FAQ
                            </a>
                        </li>
                        <li style="margin-bottom: 0.75rem;">
                            <a href="<?= $base ?>/pages/contact.php" style="color: var(--gray); text-decoration: none; transition: color 0.3s; cursor: pointer;" 
                               onmouseover="this.style.color='var(--primary)'" 
                               onmouseout="this.style.color='var(--gray)'">
                                Contact
                            </a>
                        </li>
                        <li style="margin-bottom: 0.75rem;">
                            <a href="<?= $base ?>/pages/conditions-utilisation.php" style="color: var(--gray); text-decoration: none; transition: color 0.3s; cursor: pointer;" 
                               onmouseover="this.style.color='var(--primary)'" 
                               onmouseout="this.style.color='var(--gray)'">
                                Conditions d'utilisation
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Section Compte -->
                <div>
                    <h4 style="color: white; margin-bottom: 1rem; font-size: 1.1rem;">Compte</h4>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li style="margin-bottom: 0.75rem;">
                            <a href="../connexion.php" style="color: var(--gray); text-decoration: none; transition: color 0.3s; cursor: pointer;" 
                               onmouseover="this.style.color='var(--primary)'" 
                               onmouseout="this.style.color='var(--gray)'">
                                Connexion
                            </a>
                        </li>
                        <li style="margin-bottom: 0.75rem;">
                            <a href="../inscription.php" style="color: var(--gray); text-decoration: none; transition: color 0.3s; cursor: pointer;" 
                               onmouseover="this.style.color='var(--primary)'" 
                               onmouseout="this.style.color='var(--gray)'">
                                Inscription
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Ligne de séparation -->
            <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 2rem; text-align: center;">
                <p style="color: var(--gray); font-size: 0.875rem; margin: 0;">
                    © 2026 SportConnect. Tous droits réservés.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
