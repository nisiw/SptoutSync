<?php
	include_once('config.php');

	$errorMsg = "";

	if(isset($_POST['submit']))
	{
		$name     = $_POST['name'];
		$surname  = $_POST['surname'];
		$username = $_POST['username'];
		$email    = $_POST['email'];
		$tempPass = $_POST['password'];
		$password = password_hash($tempPass, PASSWORD_DEFAULT);

		if(empty($name) || empty($surname) || empty($username) || empty($email) || empty($tempPass))
		{
			$errorMsg = "You have not filled in all the fields.";
		}
		else
		{
			$sql = "INSERT INTO users(name,surname,username,email,password) VALUES (:name, :surname, :username, :email, :password)";
			$insertSql = $conn->prepare($sql);

			$insertSql->bindParam(':name', $name);
			$insertSql->bindParam(':surname', $surname);
			$insertSql->bindParam(':username', $username);
			$insertSql->bindParam(':email', $email);
			$insertSql->bindParam(':password', $password);

			try
			{
				$insertSql->execute();
				header("Location: login.php");
				exit();
			}
			catch(PDOException $e)
			{
				if($e->getCode() === '23000')
				{
					$errorMsg = "An account with this email already exists.";
				}
				else
				{
					$errorMsg = "Registration failed. Please try again.";
				}
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SproutSync</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Work+Sans:wght@400;500;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#012d1d",
                        "primary-container": "#1b4332",
                        "surface": "#fcf9f8",
                        "on-surface": "#1b1c1c",
                        "background": "#fcf9f8",
                        "outline-variant": "#c1c8c2",
                        "error": "#ba1a1a",
                    },
                    fontFamily: {
                        sans: ['Work Sans', 'sans-serif'],
                        heading: ['Manrope', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-background text-on-surface font-sans antialiased min-h-screen flex justify-center">

<div class="w-full max-w-[430px] flex flex-col min-h-screen shadow-2xl bg-surface relative">
    
    <!-- Header -->
    <header class="flex items-center justify-between px-6 py-5">
        <a href="index.php" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white">
                <span class="material-symbols-outlined filled text-2xl">eco</span>
            </div>
            <span class="font-heading font-extrabold text-xl text-primary tracking-tight">SproutSync</span>
        </a>
        <button class="text-primary hover:opacity-70 transition-opacity">
            <span class="material-symbols-outlined text-2xl">notifications</span>
        </button>
    </header>

    <!-- Intro Section -->
    <section class="px-6 pt-2 pb-4">
        <h1 class="font-heading font-extrabold text-3xl text-primary leading-tight mb-2">Create Account</h1>
        <p class="text-on-surface opacity-60 text-sm leading-relaxed">Join the SproutSync community and start monitoring your urban jungle today.</p>
    </section>

    <!-- Form Section -->
    <main class="flex-grow px-6 pt-4">
        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-outline-variant/30">
            
            <?php if(!empty($errorMsg)): ?>
                <div class="mb-5 bg-error/10 border border-error/20 text-error px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">error</span>
                    <?php echo htmlspecialchars($errorMsg); ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-primary/60 ml-1">First Name</label>
                        <input type="text" name="name" required class="w-full bg-surface border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary transition-all placeholder:text-primary/20" placeholder="John">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-primary/60 ml-1">Last Name</label>
                        <input type="text" name="surname" required class="w-full bg-surface border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary transition-all placeholder:text-primary/20" placeholder="Doe">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold uppercase tracking-widest text-primary/60 ml-1">Username</label>
                    <input type="text" name="username" required class="w-full bg-surface border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary transition-all placeholder:text-primary/20" placeholder="johndoe77">
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold uppercase tracking-widest text-primary/60 ml-1">Email Address</label>
                    <input type="email" name="email" required class="w-full bg-surface border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary transition-all placeholder:text-primary/20" placeholder="hello@example.com">
                </div>

                <div class="space-y-1.5 pb-2">
                    <label class="text-[10px] font-bold uppercase tracking-widest text-primary/60 ml-1">Password</label>
                    <input type="password" name="password" required class="w-full bg-surface border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-primary transition-all placeholder:text-primary/20" placeholder="••••••••">
                </div>

                <button type="submit" name="submit" class="w-full py-4 bg-primary text-white rounded-2xl font-heading font-bold text-sm uppercase tracking-widest shadow-lg shadow-primary/20 hover:bg-primary-container transition-all active:scale-[0.98] mt-4">
                    Sign Up
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-[13px] text-on-surface opacity-60">Already have an account? 
                    <a href="login.php" class="text-primary font-bold hover:underline">Login here</a>
                </p>
            </div>
        </div>
        
        <!-- Social Sign Up (Visual Only) -->
        <div class="mt-8 px-2">
            <div class="relative flex items-center justify-center mb-6">
                <div class="absolute w-full border-t border-outline-variant/50"></div>
                <span class="relative px-4 bg-surface text-[10px] font-bold uppercase tracking-widest text-primary/40">Or register with</span>
            </div>
            
            <div class="flex gap-4 justify-center">
                <button class="w-14 h-14 rounded-2xl bg-white border border-outline-variant/30 flex items-center justify-center shadow-sm hover:shadow-md transition-all active:scale-90" title="Google">
                    <svg class="w-6 h-6" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                </button>
                <button class="w-14 h-14 rounded-2xl bg-white border border-outline-variant/30 flex items-center justify-center shadow-sm hover:shadow-md transition-all active:scale-90" title="Apple">
                    <svg class="w-6 h-6" viewBox="0 0 24 24"><path d="M17.05 20.28c-.96.95-2.12 2.21-3.6 2.21-1.42 0-1.89-.87-3.6-.87-1.72 0-2.24.84-3.6.87-1.45.03-2.77-1.42-3.73-2.38-1.97-1.96-3.47-5.54-3.47-8.91 0-3.37 1.74-5.15 3.4-5.15 1.58 0 2.45.93 3.52.93 1.06 0 1.77-.93 3.52-.93 1.34 0 2.58.64 3.23 1.55-3.3 1.37-2.77 6.02.58 7.37-.6 1.53-1.38 3.03-2.25 4.31zM12.03 4.52c-.06-1.91 1.58-3.76 3.47-3.52.22 2.15-1.87 4.14-3.47 3.52z"/></button>
            </div>
        </div>
    </main>

    <footer class="p-8 text-center">
        <p class="text-[10px] text-primary/30 uppercase tracking-[0.2em]">SproutSync Botanical Intelligence v2.0</p>
    </footer>
</div>

<script>
    // Just a fun little interaction for the social buttons
    document.querySelectorAll('button[title]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if(this.type === 'submit') return;
            e.preventDefault();
            const provider = this.getAttribute('title');
            alert(provider + ' sign-up is coming soon! 🌱');
        });
    });
</script>
</body>
</html>
