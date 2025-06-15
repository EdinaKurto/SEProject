# webproject
Welcome!

Our names are Edina Kurto and Arman Ćulah and this is our repo for our student project "Smell of East and West shop: Aragon" for the course IT309 Software Engineering at the International Burch University. Website hosted on: . <br>

March 2025: <br>
Layed out the core architecture of our project, creating frontend and backend folders accordingly <br>
Enabled SPApp using https://github.com/amiletti/spapp <br>
Created the static frontend for our project, which includes a dashboard, login/signup, browsing page, product page, profile (for admin and for the user) with a table for orders and admin consoles, the cart that leads to the checkout, conected through the index.html. <br>
Used Bootstrap, but not specifically a template; to fit our needs and not to clutter this project <br>
<br>
Details on the Database ERD can be found in the official documentation of the website. <br>
April 2025: <br>
New database using MySQL and tuned the prior diagrams to the website, adding a new payment table to handle the payment information in orders, and tuned the junction tables<br>
DAO layer in the backend, using the approach of creating a basedao then extending the basedao to other table specific dao files. This method prevents SQL injections and provides flexibility and scaleability <br>
Users have two different roles: Admin and User, with different Authorization methods. <br>

For the first deliverable, Milestone 1, we created our plan of action, diagrams and user stories for the project. They also can be found in the project documentation <br>

 For the second deliverable, according to our set plan, we completed the frontend of the project and created the structure of our backend, following our set plan by Milestone 1. We tested routes and had a fully working DAO layer. We implemented Swagger documentation. This is set under Version1 of the project.<br>

