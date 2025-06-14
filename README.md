# webproject
Welcome!
My name is Arman and this is my repo for my student Web project for the course IT207 at International Burch University Sarajevo. My website is called "Aragon Fragrance" and is about locally made, pure fragrances.

Milestone 1, 23rd of March 2025: <br>
-I have layed out the core architecture of my project, creating frontend and backend folders accordingly <br>
-I have enabled SPApp using https://github.com/amiletti/spapp <br>
-I have created the static frontend for my project, which includes a dashboard, login/signup, browsing page, product page, profile (for admin and for the user) with a table for orders and admin consoles, the cart that leads to the checkout, conected through the index.html. <br>
I have used Bootstrap, but not specifically a template; to fit my needs and not to clutter this project, I have decided to create views and features individually that I will use for the next milestones, I have created different blocks of features all integrated through a common design and stylesheet. <br>
-I have put the ERD diagram for the project in the folder called deliverables, subfolder ERD <br>

Milestone 2, 6th of April 2025: <br>
-I have created a new database using MySQL and tuned the prior diagrams to the website, adding a new payment table to handle the payment information in orders, and tuned the junction tables (everyone is a little bad at databases :D ) <br>
-I have created a DAO layer in the backend, using the approach of creating a basedao then extending the basedao to other table specific dao files. I had to change the suggested basedao file to fix the assuption that ID is the primary key, since every key I named was different. But then, composite keys had to be handled manually. The database connectivity is inside the config file. In the daotests folder are some scripts used to test out the connection and populate the database. I passed all important CRUD tests. <br> This method prevents SQL injections and provides flexibility and scaleability (Perfect for us who will realise some mistakes in the database :D ) You can find the database dump in the new PR as well. <br>

Timeline:
Week 1: Initial setup
I enabled my server, and connected to my repo. Started working on the fronted of the webpage <br>
Week 2: Break due to sickness and personal events <br>
Week 3: Fronted done, preparing for Milestone II <br>
Week 4 - 6: DAO layer completed, working on Milestone 3  <br>

Milestone 4 compleated <br>
Milestone 5, website is deployed, frontend not connected <br>
<br>
website link: https://aragon-frontend-wm6hn.ondigitalocean.app/#profile

