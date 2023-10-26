# dt42
Stock Information block

Exercise

    #Stock Information Block

        Requirement is to create a block to display the stock information of a company. The information that needs to be shown

            Stock Name

            Stock Description

            Highest Price

            Lowest Price

        Create a custom block which should have a configuration form for below fields
        Company Symbol (ticker) - for e.g, AAPL for Apple, AMZN for Amazon, GOOG for Alphabet etc

            Start Date

            End Date 

            This block will show the details of particular stock. Fetch the stock details using

            For Stock Name & Description - https://api.tiingo.com/tiingo/daily/aapl?token=34f412d51db4046a81f4180aad2233c41df5d3b1

            For stock prices - https://api.tiingo.com/tiingo/daily/aapl/prices?startDate=2022-1-30&endDate=2022-2-1&token=34f412d51db4046a81f4180aad2233c41df5d3b1

        If no start date and end date are provided use the current date
        Replace aapl with the ticker defined in the block configuration
        Create Instances of the block with different sets of values and place them at different spots on the site.

    #Temperature Block

        In the Form API exercise a configuration form was created to store Country, City, Temperature API configurations.

        Now, create a custom block which

            Fetches these configurations
            Call the temperature API
            Shows the temperature of that city
