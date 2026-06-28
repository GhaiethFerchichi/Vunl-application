FROM python:3.8
ADD . /app
WORKDIR /app
EXPOSE 8000
CMD ["python3", "-m", "http.server", "8000"]
