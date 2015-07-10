import pymysql.cursors  # I'm using pymysql, because it works better than mysql


# Setup stuff for the mySQL connector
def create_connection():
    cnx = pymysql.connect(      # So these are the credentials we are using to connect to the database
        user='macse_rberman',           # This is my username.  You should set one up for yourself! Email me for help.
        password='npUJgHoFmV2U3xKvtpKt',# Super secure.
        host='speak-easy.me',           # Make sure that you don't use www. or anything like that.
        database='macse_datacollection' # This changes, depending on which database you want to connect to.
    )                                   # I usually make the database a paramater, so it's easily changed.
    cursor = cnx.cursor()  # This is the cursor, it's what we use to execute queries and access the information
    return cnx, cursor


# Here is a sample method of what we might do with sql:
def sample(cursor):
    # This is how you'd execute any query, whether it's an update, deletion, or whatever.
    # The query is simply a string (so string concatenation and stuff is totally acceptable) that mysql will execute
    cursor.execute("SELECT id, email FROM interestscollection WHERE soccer = True")

    # Now that I selected some stuff, I can loop through it and check out the result.
    # Note: using this syntax, you must loop through everything in the cursor, or it will get mad
    # There are other ways to loop through (like fetchall()), but I find this to be the easiest to work with.
    for (id, email) in cursor:  # Make sure to use a tuple here, otherwise it returns a tuple, not a value, which is a mess
        print(id)   # You can do whatever you want with the variables.  Make a list! Or a dictionary! Party it up!
        print(email)

    # Note: If you are editing the database in any way, make sure to commit your changes.
    # After the cursor.execute line, put "cnx.commit()" so that the database actually reflects the changes you made
    # This is not for select queries - only things that modify the database


# So here's the part that gets run...
def main():
    cnx, cursor = create_connection()   # The first thing we do is create a connection and a cursor to pass to functions

    sample(cursor)  # Call the sample method (and pass it the cursor - so it has the db connection)

    cursor.close()  # Make sure to close the cursor and connection at the end of the session!
    cnx.close()

main()  # BAM