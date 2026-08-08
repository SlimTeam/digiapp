import re
from pathlib import Path

replacements = {
    'lang="en"': 'lang="fr"',
    'DigiApp Services | Employee Leave Management System': 'DigiApp Services | Gestion des congés employé',
    'Welcome to Employee Leave Management System': 'Bienvenue dans le système de gestion des congés',
    'Employee Login': 'Connexion employé',
    'Employe Login': 'Connexion employé',
    'Emp Password Recovery': 'Récupération mot de passe employé',
    'Admin Login': 'Connexion administrateur',
    'Employee leave management system |  Admin': 'Système de gestion des congés | Admin',
    'Employee Leave Management System | Admin Login': 'Système de gestion des congés | Connexion administrateur',
    'Employee | Leave History': 'Employé | Historique des congés',
    'Employee | Change Password': 'Employé | Changer le mot de passe',
    'Employe | Apply Leave': 'Employé | Demander un congé',
    'Email Id': 'Email',
    'Sign In': 'Se connecter',
    'Sign in': 'Se connecter',
    'Sign Out': 'Déconnexion',
    'Invalid Details': 'Identifiants invalides',
    'Your account is Inactive. Please contact admin': 'Votre compte est inactif. Veuillez contacter l\'administrateur',
    'Apply for Leave': 'Demande de congé',
    'Apply': 'Demander',
    'From  Date': 'Date de début',
    'To Date': 'Date de fin',
    'Status': 'Statut',
    'Description': 'Description',
    'Error': 'Erreur',
    'ERROR': 'ERREUR',
    'SUCCESS': 'SUCCÈS',
    'Success': 'Succès',
    'My Profiles': 'Mon profil',
    'Chnage Password': 'Changer le mot de passe',
    'Leaves': 'Congés',
    'Leave History': 'Historique des congés',
    'Pending Leaves': 'Congés en attente',
    'Approved Leaves': 'Congés approuvés',
    'Not Approved Leaves': 'Congés non approuvés',
    'Change Password': 'Changer le mot de passe',
    'Confirm Password': 'Confirmer le mot de passe',
    'Current Password': 'Mot de passe actuel',
    'New Password': 'Nouveau mot de passe',
    'Admin Action taken date :': 'Date de l\'action administrateur :',
    'Admin Remark:': 'Remarque admin :',
    'Emp Contact No. :': 'Téléphone employé :',
    'Emp Email id :': 'Email employé :',
    'Emp Id :': 'ID employé :',
    'Employe Leave Description :': 'Description du congé :',
    'Employe Name :': 'Nom de l\'employé :',
    'From': 'Du',
    'To': 'Au',
    'Leave Date . :': 'Date de congé :',
    'Leave Details': 'Détails du congé',
    'Leave Type :': 'Type de congé :',
    'Leave take action': 'Action sur le congé',
    'Take&nbsp;Action': 'Prendre une décision',
    'waiting for approval': 'en attente d\'approbation',
    'Not Approved': 'Non approuvé',
    'Approved': 'Approuvé',
    'Pending Leave History': 'Historique des congés en attente',
    'Approved Leave History': 'Historique des congés approuvés',
    'Not Approved Leave History': 'Historique des congés non approuvés',
    'Add Department': 'Ajouter un département',
    'Department Created Successfully': 'Département créé avec succès',
    'Add employee': 'Ajouter un employé',
    'Employee record added Successfully': 'Employé ajouté avec succès',
    'Add Leave Type': 'Ajouter un type de congé',
    'Leave type added Successfully': 'Type de congé ajouté avec succès',
    'Update Department': 'Mettre à jour le département',
    'Department updated Successfully': 'Département mis à jour avec succès',
    'Update employee': 'Mettre à jour l\'employé',
    'Employee record updated Successfully': 'Employé mis à jour avec succès',
    'Edit Leave Type': 'Modifier le type de congé',
    'Leave type updated Successfully': 'Type de congé mis à jour avec succès',
    'Total Leave': 'Congés totaux',
    'Creation Date': 'Date de création',
    'Departments Info': 'Informations sur les départements',
    'Dept Code': 'Code dép.',
    'Dept Name': 'Nom dép.',
    'Dept Short Name': 'Nom dép. abrégé',
    'Employees Info': 'Informations employés',
    'Full Name': 'Nom complet',
    'Reg Date': 'Date d\'inscription',
    'Active': 'Actif',
    'Inactive': 'Inactif',
    'Admin | Dashboard': 'Admin | Tableau de bord',
    'Admin | Add Department': 'Admin | Ajouter un département',
    'Admin | Add Employee': 'Admin | Ajouter un employé',
    'Admin | Add Leave Type': 'Admin | Ajouter un type de congé',
    'Admin | Approved Leaves': 'Admin | Congés approuvés',
    'Admin | Change Password': 'Admin | Changer le mot de passe',
    'Admin | Leave Details': 'Admin | Détails du congé',
    'Admin | Total Leave': 'Admin | Congés',
    'Admin | Manage Departments': 'Admin | Gérer les départements',
    'Admin | Manage Employees': 'Admin | Gérer les employés',
    'Admin | Manage Leave Type': 'Admin | Gérer les types de congés',
    'Admin | Not Approved Leaves': 'Admin | Congés non approuvés',
    'Admin | Approved Leave leaves': 'Admin | Congés en attente',
    'Admin | Update Department': 'Admin | Mettre à jour le département',
    'Admin | Update Employee': 'Admin | Mettre à jour l\'employé',
    'Admin | Edit Leave Type': 'Admin | Modifier le type de congé',
    'Username': 'Nom d\'utilisateur',
    'Leave Type': 'Type de congé',
    'Action': 'Action',
    'Admin | Approved Leaves': 'Admin | Congés approuvés',
    'Admin | Not Approved Leaves': 'Admin | Congés non approuvés',
    'Admin | Approved Leave leaves': 'Admin | Congés en attente',
    'Admin | Total Leave': 'Admin | Tous les congés',
    'Admin | Add Leave Type': 'Admin | Ajouter un type de congé',
    'Admin | Manage Employees': 'Admin | Gérer les employés',
    'Admin | Manage Leave Type': 'Admin | Gérer les types de congés',
    'Your Password succesfully changed': 'Votre mot de passe a été modifié avec succès',
    'Your current password is wrong': 'Votre mot de passe actuel est incorrect',
    'Employee id already exists .': 'L\'ID employé existe déjà.',
    'Employee id available for Registration .': 'ID employé disponible pour l\'enregistrement.',
    'Email id already exists .': 'L\'email existe déjà.',
    'Email id available for Registration .': 'Email disponible pour l\'enregistrement.',
    'ToDate should be greater than FromDate': 'La date de fin doit être postérieure à la date de début',
    'Choose your option': 'Choisissez votre option',
    'Take&nbsp;Action': 'Prendre une décision',
    'Admin Action taken date :': 'Date de l\'action administrateur :',
    'Admin Remark:': 'Remarque admin :',
    'Emp Contact No. :': 'Téléphone employé :',
    'Emp Email id :': 'Email employé :',
    'Emp Id :': 'ID employé :',
    'Employe Leave Description :': 'Description du congé :',
    'Employe Name :': 'Nom de l\'employé :',
    'Leave Date . :': 'Date de congé :',
    'Leave take action': 'Action sur le congé',
}

pattern = re.compile(r'(?<=">)(.*?)(?=<)')

files = list(Path('.').glob('*.php')) + list(Path('admin').glob('*.php'))
for path in files:
    text = path.read_text(encoding='utf-8', errors='ignore')
    new_text = text
    for old, new in replacements.items():
        # replace visible text in tags
        new_text = new_text.replace(f'>{old}<', f'>{new}<')
        # replace in title tags
        new_text = new_text.replace(f'<title>{old}</title>', f'<title>{new}</title>')
        # replace in value attributes
        new_text = new_text.replace(f'value="{old}"', f'value="{new}"')
        new_text = new_text.replace(f"value='{old}'", f"value='{new}'")
        # replace in label tags
        new_text = new_text.replace(f'<label for="', f'<label for="')
        new_text = new_text.replace(f'>{old}</label>', f'>{new}</label>')
        # replace inside link text and spans
        new_text = new_text.replace(f'>{old}</a>', f'>{new}</a>')
        new_text = new_text.replace(f'>{old}</span>', f'>{new}</span>')
        new_text = new_text.replace(f'>{old}</button>', f'>{new}</button>')
        new_text = new_text.replace(f'>{old}</h1>', f'>{new}</h1>')
        new_text = new_text.replace(f'>{old}</h2>', f'>{new}</h2>')
        new_text = new_text.replace(f'>{old}</h3>', f'>{new}</h3>')
        new_text = new_text.replace(f'>{old}</p>', f'>{new}</p>')
        new_text = new_text.replace(f'>{old}</div>', f'>{new}</div>')
        new_text = new_text.replace(f'alert("{old}")', f'alert("{new}")')
        new_text = new_text.replace(f"alert('{old}')", f"alert('{new}')")
    if new_text != text:
        path.write_text(new_text, encoding='utf-8')
        print(f'Updated {path}')
